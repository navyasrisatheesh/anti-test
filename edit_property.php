<?php include 'header.php'; ?>
<?php
require_once 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$owner_id = $_SESSION['user_id'];

// Fetch the property
$propRes = $conn->query("SELECT * FROM Properties WHERE PropertyID = $id AND OwnerID = $owner_id");
if($propRes->num_rows == 0) {
    echo "<div class='container'><div class='alert alert-danger'>Property not found or access denied.</div></div>";
    include 'footer.php';
    exit();
}
$property = $propRes->fetch_assoc();

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = sanitize($conn, $_POST['location']);
    $new_price = floatval($_POST['price']);
    $facilities = sanitize($conn, $_POST['facilities']);
    
    $old_price = $property['Price'];
    $status = $property['Status'];

    $stmt = $conn->prepare("UPDATE Properties SET Location = ?, Price = ?, Facilities = ? WHERE PropertyID = ? AND OwnerID = ?");
    $stmt->bind_param("sdsii", $location, $new_price, $facilities, $id, $owner_id);
    
    if($stmt->execute()) {
        $msg = "Property updated successfully!";
        $msgType = "success";
        
        // Notify seekers if the price has changed and the property is active
        if ($old_price != $new_price && $status == 'Active') {
            $notificationMsg = "Price changed for property at: $location! New price is ₹" . number_format($new_price) . ".";
            $conn->query("INSERT INTO Notifications (UserID, Message) SELECT UserID, '$notificationMsg' FROM Users WHERE Role='Seeker'");
        }

        // Update the current property array for the form
        $property['Location'] = $location;
        $property['Price'] = $new_price;
        $property['Facilities'] = $facilities;

    } else {
        $msg = "Failed to update property.";
        $msgType = "danger";
    }
}
?>

<div class="form-container" style="max-width: 600px;">
    <h2><i class="fa-solid fa-pen-to-square" style="color:var(--primary); margin-right:10px;"></i> Edit Property</h2>
    
    <?php if($msg): ?>
        <div class="alert alert-<?php echo $msgType; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label>Location / Full Address</label>
            <input type="text" name="location" required value="<?php echo htmlspecialchars($property['Location']); ?>">
        </div>
        
        <div class="form-group">
            <label>Monthly Rent (₹)</label>
            <input type="number" name="price" required value="<?php echo htmlspecialchars($property['Price']); ?>" min="1000">
        </div>
        
        <div class="form-group">
            <label>Facilities & Amenities</label>
            <textarea name="facilities" required rows="4"><?php echo htmlspecialchars($property['Facilities']); ?></textarea>
            <small style="color:var(--text-muted); display:block; margin-top:5px;">Separate different amenities securely. It’ll be displayed nicely.</small>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width:100%; font-size: 1.1rem; padding: 1rem;"><i class="fa-solid fa-save"></i> Save Changes</button>
        <a href="dashboard_owner.php" class="btn btn-secondary" style="width:100%; font-size: 1.1rem; padding: 1rem; display: block; text-align: center; margin-top: 1rem;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </form>
</div>
<?php include 'footer.php'; ?>
