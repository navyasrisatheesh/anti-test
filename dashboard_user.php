<?php include 'header.php'; ?>
<?php
require_once 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Seeker') {
    header("Location: login.php");
    exit();
}

// Handle Notifications
$user_id = $_SESSION['user_id'];
if(isset($_GET['mark_read'])) {
    $nid = intval($_GET['mark_read']);
    $conn->query("UPDATE Notifications SET IsRead=1 WHERE NotificationID=$nid AND UserID=$user_id");
}

?>

<div class="container" style="max-width: 1000px;">
    <div style="margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
        <h2 style="color:var(--secondary); font-size:2.5rem;"><i class="fa-solid fa-user-circle" style="color:var(--primary); margin-right:10px;"></i> Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
        <p style="color:var(--text-muted); font-size:1.1rem; margin-top:0.5rem;">View your notifications and recent activities.</p>
    </div>

    <!-- Notifications Section -->
    <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border);">
        <h3 style="margin-bottom:1.5rem; color:var(--secondary); display:flex; align-items:center;">
            <i class="fa-solid fa-bell" style="margin-right:10px; color:var(--accent);"></i> In-App Notifications
        </h3>
        
        <?php
        $res = $conn->query("SELECT * FROM Notifications WHERE UserID=$user_id ORDER BY CreatedAt DESC LIMIT 10");
        if($res->num_rows > 0) {
            while($row = $res->fetch_assoc()) {
                $bg = $row['IsRead'] ? 'var(--white)' : '#f8fafc';
                $fw = $row['IsRead'] ? '400' : '600';
                $iconColor = $row['IsRead'] ? 'var(--text-muted)' : 'var(--primary)';
                
                echo "<div style='background:$bg; border-left: 4px solid $iconColor; padding: 1rem 1.5rem; margin-bottom: 1rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display:flex; justify-content:space-between; align-items:flex-start;'>";
                echo "<div>";
                echo "<p style='font-weight:$fw; color:var(--text-main); line-height:1.4;'><i class='fa-solid fa-envelope' style='margin-right:8px; color:$iconColor;'></i>" . htmlspecialchars($row['Message']) . "</p>";
                echo "<small style='color:var(--text-muted); margin-top:0.5rem; display:block;'><i class='fa-regular fa-clock'></i> " . date('M j, Y g:i A', strtotime($row['CreatedAt'])) . "</small>";
                echo "</div>";
                if(!$row['IsRead']) {
                    echo "<a href='?mark_read={$row['NotificationID']}' style='color:var(--primary); font-size:0.9rem; font-weight:600;'><i class='fa-solid fa-check'></i> Mark Read</a>";
                }
                echo "</div>";
            }
        } else {
            echo "<div style='text-align:center; padding: 3rem;'><i class='fa-regular fa-bell-slash' style='font-size:3rem; color:var(--text-muted); opacity:0.5; margin-bottom:1rem;'></i><p style='color:var(--text-muted);'>No new notifications. You're all caught up!</p></div>";
        }
        ?>
    </div>
</div>
<?php include 'footer.php'; ?>
