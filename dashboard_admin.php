<?php include 'header.php'; ?>
<?php
require_once 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

$msg = '';

// Actions
if(isset($_GET['approve_user'])) {
    $id = intval($_GET['approve_user']);
    $conn->query("UPDATE Users SET Status='Approved' WHERE UserID=$id");
    $conn->query("INSERT INTO Notifications (UserID, Message) VALUES ($id, 'Your Owner account has been approved by Admin.')");
    $msg = 'User Approved successfully';
}
if(isset($_GET['block_user'])) {
    $id = intval($_GET['block_user']);
    $conn->query("UPDATE Users SET Status='Blocked' WHERE UserID=$id");
    $msg = 'User Blocked';
}

if(isset($_GET['approve_prop'])) {
    $id = intval($_GET['approve_prop']);
    
    // Find owner to notify
    $prop = $conn->query("SELECT OwnerID, Location FROM Properties WHERE PropertyID=$id");
    if($prop->num_rows > 0) {
        $p = $prop->fetch_assoc();
        $oid = $p['OwnerID'];
        $loc = $p['Location'];
        $conn->query("INSERT INTO Notifications (UserID, Message) VALUES ($oid, 'Your property at $loc has been approved and is now Active!')");
        
        // Notify seekers about new property matching maybe
        $conn->query("INSERT INTO Notifications (UserID, Message) SELECT UserID, 'New property listed in $loc! Check it out.' FROM Users WHERE Role='Seeker'");
    }
    
    $conn->query("UPDATE Properties SET Status='Active' WHERE PropertyID=$id");
    $msg = 'Property Approved successfully';
}

if(isset($_GET['del_prop'])) {
    $id = intval($_GET['del_prop']);
    $conn->query("DELETE FROM Properties WHERE PropertyID=$id");
    $msg = 'Property removed.';
}

if(isset($_GET['del_rev'])) {
    $id = intval($_GET['del_rev']);
    $conn->query("DELETE FROM Reviews WHERE ReviewID=$id");
    $msg = 'Review removed.';
}

// Analytics
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM Users")->fetch_assoc()['c'];
$totalProps = $conn->query("SELECT COUNT(*) as c FROM Properties")->fetch_assoc()['c'];
$totalRevs = $conn->query("SELECT COUNT(*) as c FROM Reviews")->fetch_assoc()['c'];
$pendingProps = $conn->query("SELECT COUNT(*) as c FROM Properties WHERE Status='Pending'")->fetch_assoc()['c'];

?>

<div class="container" style="max-width: 1300px;">
    <?php if($msg): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check"></i> <?php echo $msg; ?></div>
    <?php endif; ?>
    
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem;">
        <div>
            <h2 style="color:var(--secondary); font-size:2.5rem;"><i class="fa-solid fa-shield-halved" style="color:var(--primary); margin-right:10px;"></i> Admin Dashboard</h2>
            <p style="color:var(--text-muted); font-size:1.1rem; margin-top:0.5rem;">System overview and moderation controls.</p>
        </div>
    </div>
    
    <!-- Analytics -->
    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:2rem; margin-bottom: 3rem;">
        <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border); text-align:center;">
            <i class="fa-solid fa-users" style="font-size:2.5rem; color:var(--primary); margin-bottom:1rem;"></i>
            <h3 style="font-size:2rem; color:var(--secondary);"><?php echo $totalUsers; ?></h3>
            <p style="color:var(--text-muted);">Total Users</p>
        </div>
        <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border); text-align:center;">
            <i class="fa-solid fa-building" style="font-size:2.5rem; color:#f59e0b; margin-bottom:1rem;"></i>
            <h3 style="font-size:2rem; color:var(--secondary);"><?php echo $totalProps; ?></h3>
            <p style="color:var(--text-muted);">Properties Listed</p>
        </div>
        <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border); text-align:center;">
            <i class="fa-solid fa-star" style="font-size:2.5rem; color:#ef4444; margin-bottom:1rem;"></i>
            <h3 style="font-size:2rem; color:var(--secondary);"><?php echo $totalRevs; ?></h3>
            <p style="color:var(--text-muted);">Total Reviews</p>
        </div>
        <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border); text-align:center;">
            <i class="fa-solid fa-clock-rotate-left" style="font-size:2.5rem; color:#3b82f6; margin-bottom:1rem;"></i>
            <h3 style="font-size:2rem; color:var(--secondary);"><?php echo $pendingProps; ?></h3>
            <p style="color:var(--text-muted);">Pending Approvals</p>
        </div>
    </div>
    
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:3rem; margin-bottom: 3rem;">
        <!-- Users Management -->
        <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border);">
            <h3 style="margin-bottom:1.5rem; color:var(--secondary); border-bottom:1px solid var(--border); padding-bottom:1rem;">
                <i class="fa-solid fa-users-gear"></i> Manage Users
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <tr><th>Name</th><th>Role</th><th>Status</th><th>Action</th></tr>
                    <?php
                    $res = $conn->query("SELECT * FROM Users WHERE Role != 'Admin' ORDER BY CreatedAt DESC LIMIT 10");
                    if($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['Name']}</td>
                                <td>{$row['Role']}</td>
                                <td><span class='badge ".($row['Status']=='Approved'?'badge-success':($row['Status']=='Pending'?'badge-warning':'badge-danger'))."'>{$row['Status']}</span></td>
                                <td>";
                            if($row['Status'] == 'Pending') {
                                echo "<a href='?approve_user={$row['UserID']}' class='badge badge-success' style='margin-right:5px;'>Approve</a>";
                            }
                            if($row['Status'] != 'Blocked') {
                                echo "<a href='?block_user={$row['UserID']}' class='badge badge-danger'>Block</a>";
                            }
                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center;'>No users found.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
        
        <!-- Properties Management -->
        <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border);">
            <h3 style="margin-bottom:1.5rem; color:var(--secondary); border-bottom:1px solid var(--border); padding-bottom:1rem;">
                <i class="fa-solid fa-building-circle-exclamation"></i> Moderate Properties
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <tr><th>Location</th><th>Status</th><th>Action</th></tr>
                    <?php
                    $res = $conn->query("SELECT * FROM Properties ORDER BY CreatedAt DESC LIMIT 10");
                    if($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['Location']}</td>
                                <td><span class='badge ".($row['Status']=='Active'?'badge-success':'badge-warning')."'>{$row['Status']}</span></td>
                                <td>";
                            if($row['Status'] == 'Pending') {
                                echo "<a href='?approve_prop={$row['PropertyID']}' class='badge badge-success' style='margin-right:5px;'>Approve</a>";
                            }
                            echo "<a href='?del_prop={$row['PropertyID']}' class='badge badge-danger' onclick='return confirm(\"Delete this property?\")'>Remove</a>";
                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;'>No properties found.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Reviews Moderation -->
    <div style="background:var(--white); padding:2rem; border-radius:16px; box-shadow:var(--shadow); border: 1px solid var(--border);">
        <h3 style="margin-bottom:1.5rem; color:var(--secondary); border-bottom:1px solid var(--border); padding-bottom:1rem;">
            <i class="fa-solid fa-comments"></i> Moderate Reviews
        </h3>
        <div class="table-responsive">
            <table class="table">
                <tr><th>User</th><th>Rating</th><th>Comment</th><th>Action</th></tr>
                <?php
                $res = $conn->query("SELECT r.*, u.Name FROM Reviews r JOIN Users u ON r.UserID = u.UserID ORDER BY r.CreatedAt DESC LIMIT 10");
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['Name']}</td>
                            <td>{$row['Rating']}/5</td>
                            <td style='max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>{$row['Comment']}</td>
                            <td><a href='?del_rev={$row['ReviewID']}' class='btn btn-danger' style='padding:0.3rem 0.6rem; font-size:0.8rem;' onclick='return confirm(\"Delete review?\")'>Delete</a></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center;'>No reviews found.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
    
</div>
<?php include 'footer.php'; ?>
