<?php include 'header.php'; ?>
<?php
require_once 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");
    exit();
}

$prop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $amount = 500.00; // Fixed listing fee
    $stmt = $conn->prepare("INSERT INTO Payments (OwnerID, PropertyID, Amount, Status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("iid", $_SESSION['user_id'], $prop_id, $amount);
    if($stmt->execute()) {
        $msg = "Payment submitted successfully! Waiting for Admin verification.";
        header("Refresh: 2; url=dashboard_owner.php");
    }
}
?>

<div class="qr-container">
    <h2 style="color:var(--secondary); margin-bottom:1rem;"><i class="fa-solid fa-qrcode" style="color:var(--primary);"></i> Listing Fee Payment</h2>
    <p style="color:var(--text-muted); font-size:1.1rem; margin-bottom:2rem;">Pay ₹500 to activate your property listing.</p>
    
    <?php if($msg): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $msg; ?></div>
    <?php else: ?>
        <div class="qr-code"></div>
        <p style="margin-bottom:1.5rem; font-weight:600; color:var(--text-main);">Scan with any UPI App (GPay, PhonePe, Paytm)</p>
        
        <form method="POST">
            <button type="submit" name="pay" class="btn btn-primary" style="width:100%; padding: 1rem; font-size:1.1rem;"><i class="fa-solid fa-check-double"></i> I have completed the payment</button>
        </form>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
