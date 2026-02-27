<?php include 'header.php'; ?>
<?php
require_once 'db.php';
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = sanitize($conn, $_POST['role']);
    $contact = sanitize($conn, $_POST['contact']);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT UserID FROM Users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $msg = 'Email already registered. Please login.';
        $msgType = 'danger';
    } else {
        $status = ($role == 'Owner') ? 'Pending' : 'Approved';
        
        $stmt = $conn->prepare("INSERT INTO Users (Name, Email, Password, Role, ContactInfo, Status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $password, $role, $contact, $status);
        
        if($stmt->execute()) {
            if($role == 'Owner') {
                $msg = 'Registration successful! Please wait for Admin approval.';
            } else {
                $msg = 'Registration successful! You can now login.';
            }
            $msgType = 'success';
        } else {
            $msg = 'Registration failed. Try again.';
            $msgType = 'danger';
        }
    }
}
?>

<div class="form-container">
    <h2><i class="fa-solid fa-user-plus" style="color:var(--primary); margin-right:10px;"></i> Create Account</h2>
    
    <?php if($msg): ?>
        <div class="alert alert-<?php echo $msgType; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required placeholder="John Doe">
        </div>
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="john@example.com">
        </div>
        
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="contact" required placeholder="+91 9876543210">
        </div>
        
        <div class="form-group">
            <label>I am a...</label>
            <select name="role" required>
                <option value="Seeker">Seeker (Looking for PG)</option>
                <option value="Owner">Owner (Listing a PG)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        
        <button type="submit" class="btn btn-primary" style="width:100%; font-size: 1.1rem; padding: 1rem;">Register</button>
        
        <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">Already have an account? <a href="login.php" style="font-weight:600;">Login here</a></p>
    </form>
</div>
<?php include 'footer.php'; ?>
