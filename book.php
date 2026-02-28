<?php include 'header.php'; ?>
<?php
require_once 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Seeker') {
    header("Location: login.php");
    exit();
}

$prop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

// Get property and owner details
$sql = "SELECT p.*, u.Name as OwnerName, u.UserID as OwnerID FROM Properties p JOIN Users u ON p.OwnerID = u.UserID WHERE p.PropertyID = $prop_id AND p.Status = 'Active'";
$result = $conn->query($sql);

if($result->num_rows == 0) {
    echo "<div class='container' style='text-align:center; padding: 5rem;'><i class='fa-solid fa-triangle-exclamation' style='font-size:3rem; color:var(--accent); margin-bottom:1rem;'></i><h2>Property Not Found or Inactive</h2><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Go Back Home</a></div>";
    include 'footer.php';
    exit();
}

$prop = $result->fetch_assoc();
$owner_id = $prop['OwnerID'];
$amount = $prop['Price'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $stmt = $conn->prepare("INSERT INTO Bookings (PropertyID, UserID, OwnerID, Status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("iii", $prop_id, $_SESSION['user_id'], $owner_id);
    if($stmt->execute()) {
        $msg = "Payment successful! Booking request sent to the owner for verification.";
        header("Refresh: 2; url=dashboard_user.php");
    } else {
        $msg = "Error processing booking.";
    }
}
?>

<div class="qr-container" style="max-width: 500px; margin: 3rem auto; background: var(--white); padding: 3rem; border-radius: 20px; text-align: center; box-shadow: var(--shadow);">
    <h2 style="color:var(--secondary); margin-bottom:1rem;"><i class="fa-solid fa-qrcode" style="color:var(--primary);"></i> Complete Booking Payment</h2>
    <p style="color:var(--text-muted); font-size:1.1rem; margin-bottom:2rem;">Pay ₹<?php echo number_format($amount); ?> to book <strong><?php echo htmlspecialchars($prop['Location']); ?></strong>.</p>
    
    <?php if($msg): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $msg; ?></div>
    <?php else: ?>
        <div class="qr-code" style="width: 250px; height: 250px; background: url('https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg') center/cover; margin: 0 auto 2rem; border-radius: 12px; border: 10px solid #f8fafc;"></div>
        <p style="margin-bottom:1.5rem; font-weight:600; color:var(--text-main);">Scan with any UPI App (GPay, PhonePe, Paytm)</p>
        
        <form method="POST">
            <button type="submit" name="pay" class="btn btn-primary" style="width:100%; padding: 1rem; font-size:1.1rem;"><i class="fa-solid fa-check-double"></i> I have completed the payment</button>
        </form>
        <div style="margin-top: 1rem;">
            <a href="property_details.php?id=<?php echo $prop_id; ?>" style="color: var(--text-muted); text-decoration: underline;">Cancel Booking</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
