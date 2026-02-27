<?php include 'header.php'; ?>
<?php
require_once 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");
    exit();
}

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = sanitize($conn, $_POST['location']);
    $price = floatval($_POST['price']);
    $facilities = sanitize($conn, $_POST['facilities']);
    $owner_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO Properties (OwnerID, Location, Price, Facilities, Status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isds", $owner_id, $location, $price, $facilities);
    
    if($stmt->execute()) {
        $prop_id = $conn->insert_id;
        $msg = "Property added successfully! Please proceed to payment to activate your listing.";
        $msgType = "success";
        header("Refresh: 2; url=payment.php?id=$prop_id");
    } else {
        $msg = "Failed to add property.";
        $msgType = "danger";
    }
}
?>

<div class="form-container" style="max-width: 600px;">
    <h2><i class="fa-solid fa-house-medical" style="color:var(--primary); margin-right:10px;"></i> Add New Property</h2>
    
    <?php if($msg): ?>
        <div class="alert alert-<?php echo $msgType; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label>Location / Full Address</label>
            <input type="text" name="location" required placeholder="e.g. 123 MG Road, Bangalore">
        </div>
        
        <div class="form-group">
            <label>Monthly Rent (₹)</label>
            <input type="number" name="price" required placeholder="15000" min="1000">
        </div>
        
        <div class="form-group">
            <label>Facilities & Amenities</label>
            <textarea name="facilities" required rows="4" placeholder="e.g. WiFi, AC, Attached Bathroom, Food included..."></textarea>
            <small style="color:var(--text-muted); display:block; margin-top:5px;">Separate different amenities securely. It’ll be displayed nicely.</small>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width:100%; font-size: 1.1rem; padding: 1rem;"><i class="fa-solid fa-plus-circle"></i> Submit Listing</button>
    </form>
</div>
<?php include 'footer.php'; ?>
