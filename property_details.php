<?php include 'header.php'; ?>
<?php
require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT p.*, u.Name as OwnerName, u.Email, u.ContactInfo FROM Properties p JOIN Users u ON p.OwnerID = u.UserID WHERE p.PropertyID = $id AND p.Status = 'Active'";
$result = $conn->query($sql);

if($result->num_rows == 0) {
    echo "<div class='container' style='text-align:center; padding: 5rem;'><i class='fa-solid fa-triangle-exclamation' style='font-size:3rem; color:var(--accent); margin-bottom:1rem;'></i><h2>Property Not Found or Inactive</h2><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Go Back Home</a></div>";
    include 'footer.php';
    exit();
}

$prop = $result->fetch_assoc();

// Handle Review Submission
$msg = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if(!isset($_SESSION['user_id'])) {
        $msg = "Please log in to submit a review.";
    } else {
        $user_id = $_SESSION['user_id'];
        $rating = intval($_POST['rating']);
        $comment = sanitize($conn, $_POST['comment']);
        
        $stmt = $conn->prepare("INSERT INTO Reviews (PropertyID, UserID, Rating, Comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $id, $user_id, $rating, $comment);
        if($stmt->execute()) {
            $msg = "Review added successfully!";
        } else {
            $msg = "Error submitting review.";
        }
    }
}
?>

<div class="container">
    <?php if($msg): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="details-grid">
        <!-- Main Content -->
        <div>
            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200&q=80" alt="Property Image" class="main-img">
            
            <div style="background:var(--white); padding:2rem; border-radius:20px; box-shadow:var(--shadow); border:1px solid var(--border); margin-bottom: 2rem;">
                <h1 style="color:var(--secondary); font-size:2rem; margin-bottom:0.5rem;">Modern PG Building</h1>
                <p style="color:var(--text-muted); font-size:1.1rem; margin-bottom:2rem;"><i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> <?php echo htmlspecialchars($prop['Location']); ?></p>
                
                <h3 style="color:var(--secondary); margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Facilities & Amenities</h3>
                <div class="amenities">
                    <?php 
                    $facs = explode(',', $prop['Facilities']);
                    foreach($facs as $fac) {
                        if(trim($fac)) {
                            echo "<span class='amenity-tag'><i class='fa-solid fa-check' style='color:var(--primary);'></i> " . htmlspecialchars(trim($fac)) . "</span>";
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Reviews Section -->
            <div style="background:var(--white); padding:2rem; border-radius:20px; box-shadow:var(--shadow); border:1px solid var(--border);">
                <h3 style="color:var(--secondary); margin-bottom:1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Property Reviews</h3>
                
                <?php
                if(isset($_SESSION['user_id'])) {
                    echo '
                    <form method="POST" style="margin-bottom:2rem; background:#f8fafc; padding:1.5rem; border-radius:12px; border:1px solid var(--border);">
                        <h4 style="margin-bottom:1rem; color:var(--secondary);">Write a Review</h4>
                        <div class="form-group" style="margin-bottom:1rem;">
                            <select name="rating" required style="width: auto; padding:0.5rem 1rem;">
                                <option value="">Select Rating</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excellent (5)</option>
                                <option value="4">⭐⭐⭐⭐ Good (4)</option>
                                <option value="3">⭐⭐⭐ Average (3)</option>
                                <option value="2">⭐⭐ Poor (2)</option>
                                <option value="1">⭐ Terrible (1)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:1rem;">
                            <textarea name="comment" rows="3" required placeholder="Share your experience..." style="width:100%; border:1px solid var(--border); padding:1rem; border-radius:8px; resize:vertical; font-family:inherit;"></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary" style="padding: 0.8rem 1.5rem;"><i class="fa-regular fa-paper-plane"></i> Submit Review</button>
                    </form>';
                } else {
                    echo '<div class="alert alert-danger" style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b;"><i class="fa-solid fa-lock"></i> <a href="login.php" style="color:#991b1b; text-decoration:underline;">Log in</a> to write a review.</div>';
                }
                ?>

                <!-- Display Reviews -->
                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    <?php
                    $revSql = "SELECT r.*, u.Name FROM Reviews r JOIN Users u ON r.UserID = u.UserID WHERE r.PropertyID = $id ORDER BY r.CreatedAt DESC";
                    $revRes = $conn->query($revSql);
                    if($revRes->num_rows > 0) {
                        while($rev = $revRes->fetch_assoc()) {
                            $stars = str_repeat('⭐', $rev['Rating']);
                            echo "<div style='background:var(--bg); padding:1.5rem; border-radius:12px; border:1px solid var(--border);'>";
                            echo "<div style='display:flex; justify-content:space-between; margin-bottom:0.5rem;'>";
                            echo "<strong style='color:var(--secondary);'>" . htmlspecialchars($rev['Name']) . "</strong>";
                            echo "<span style='font-size:0.9rem; color:var(--text-muted);'>" . date('M j, Y', strtotime($rev['CreatedAt'])) . "</span>";
                            echo "</div>";
                            echo "<div style='margin-bottom:0.5rem; font-size:0.9rem;'>$stars</div>";
                            echo "<p style='color:var(--text-main);'>" . htmlspecialchars($rev['Comment']) . "</p>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p style='color:var(--text-muted); text-align:center; font-style:italic;'>No reviews yet. Be the first to review!</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="sidebar-card">
                <div style="text-align:center; padding-bottom:1.5rem; border-bottom:1px solid var(--border); margin-bottom:1.5rem;">
                    <span style="display:block; font-size:1.1rem; color:var(--text-muted); margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Monthly Rent</span>
                    <span style="font-size:3rem; font-weight:700; color:var(--primary); line-height:1;">₹<?php echo number_format($prop['Price']); ?></span>
                </div>
                
                <h3 style="color:var(--secondary); margin-bottom:1.5rem; display:flex; align-items:center;"><i class="fa-solid fa-address-card" style="color:var(--primary); margin-right:10px;"></i> Owner Details</h3>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <form method="POST" style="margin-bottom:1.5rem;">
                        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:1rem;">
                            <li style="display:flex; gap:1rem; align-items:center; background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid var(--border);">
                                <i class="fa-solid fa-user" style="color:var(--text-muted); font-size:1.2rem; min-width:20px; text-align:center;"></i> 
                                <span style="font-weight:600; color:var(--secondary);"><?php echo htmlspecialchars($prop['OwnerName']); ?></span>
                            </li>
                            <li style="display:flex; gap:1rem; align-items:center; background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid var(--border);">
                                <i class="fa-solid fa-envelope" style="color:var(--text-muted); font-size:1.2rem; min-width:20px; text-align:center;"></i> 
                                <span><a href="mailto:<?php echo htmlspecialchars($prop['Email']); ?>" style="color:var(--primary); font-weight:600;"><?php echo htmlspecialchars($prop['Email']); ?></a></span>
                            </li>
                            <li style="display:flex; gap:1rem; align-items:center; background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid var(--border);">
                                <i class="fa-solid fa-phone" style="color:var(--text-muted); font-size:1.2rem; min-width:20px; text-align:center;"></i> 
                                <span><a href="tel:<?php echo htmlspecialchars($prop['ContactInfo']); ?>" style="color:var(--primary); font-weight:600;"><?php echo htmlspecialchars($prop['ContactInfo']); ?></a></span>
                            </li>
                        </ul>
                    </form>
                    <a href="tel:<?php echo htmlspecialchars($prop['ContactInfo']); ?>" class="btn btn-primary" style="width:100%; text-align:center; padding: 1rem; font-size:1.1rem; margin-top:1rem;"><i class="fa-solid fa-phone-volume"></i> Call Owner Now</a>
                <?php else: ?>
                    <div style="background: rgba(15, 118, 110, 0.05); padding: 2rem; border-radius: 12px; text-align: center; border: 1px dashed var(--primary);">
                        <i class="fa-solid fa-lock" style="font-size:2rem; color:var(--primary); margin-bottom:1rem;"></i>
                        <h4 style="color:var(--secondary); margin-bottom:0.5rem;">Contact Information Hidden</h4>
                        <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom:1.5rem;">Please log in to your account to view the owner's details.</p>
                        <a href="login.php" class="btn btn-primary" style="width:100%;"><i class="fa-solid fa-right-to-bracket"></i> Login Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
