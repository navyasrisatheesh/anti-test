<?php include 'header.php'; ?>
<?php
require_once 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");
    exit();
}

if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM Properties WHERE PropertyID=$id AND OwnerID={$_SESSION['user_id']}");
}

if(isset($_GET['verify_booking'])) {
    $bid = intval($_GET['verify_booking']);
    $conn->query("UPDATE Bookings SET Status = 'Confirmed' WHERE BookingID=$bid AND OwnerID={$_SESSION['user_id']}");
    $bInfo = $conn->query("SELECT UserID, PropertyID FROM Bookings WHERE BookingID=$bid")->fetch_assoc();
    if($bInfo) {
        $pInfo = $conn->query("SELECT Location FROM Properties WHERE PropertyID={$bInfo['PropertyID']}")->fetch_assoc();
        $msg = "Your booking payment for " . $conn->real_escape_string($pInfo['Location']) . " has been verified and confirmed!";
        $conn->query("INSERT INTO Notifications (UserID, Message) VALUES ({$bInfo['UserID']}, '$msg')");
    }
}

if(isset($_GET['reject_booking'])) {
    $bid = intval($_GET['reject_booking']);
    $conn->query("UPDATE Bookings SET Status = 'Rejected' WHERE BookingID=$bid AND OwnerID={$_SESSION['user_id']}");
    $bInfo = $conn->query("SELECT UserID, PropertyID FROM Bookings WHERE BookingID=$bid")->fetch_assoc();
    if($bInfo) {
        $pInfo = $conn->query("SELECT Location FROM Properties WHERE PropertyID={$bInfo['PropertyID']}")->fetch_assoc();
        $msg = "Your booking payment for " . $conn->real_escape_string($pInfo['Location']) . " has been rejected by the owner.";
        $conn->query("INSERT INTO Notifications (UserID, Message) VALUES ({$bInfo['UserID']}, '$msg')");
    }
}
?>

<div class="container" style="max-width: 1200px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 2.5rem; border-bottom: 2px solid var(--border); padding-bottom: 1.5rem;">
        <div>
            <h2 style="color:var(--secondary); font-size: 2.5rem;"><i class="fa-solid fa-building-user" style="color:var(--primary); margin-right:10px;"></i> Owner Dashboard</h2>
            <p style="color:var(--text-muted); font-size:1.1rem; margin-top:0.5rem;">Manage your property listings and payments.</p>
        </div>
        <a href="add_property.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 1.5rem;"><i class="fa-solid fa-plus"></i> Add New Property</a>
    </div>

    <div style="background:var(--white); border-radius:16px; box-shadow:var(--shadow); padding: 2rem; border: 1px solid var(--border); margin-bottom: 2rem;">
        <h3 style="margin-bottom:1.5rem; color:var(--secondary);"><i class="fa-solid fa-bookmark"></i> Property Bookings</h3>
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>Property</th>
                    <th>Seeker Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php
                $owner_id = $_SESSION['user_id'];
                $bsql = "SELECT b.*, p.Location, u.Name as SeekerName FROM Bookings b JOIN Properties p ON b.PropertyID = p.PropertyID JOIN Users u ON b.UserID = u.UserID WHERE b.OwnerID = $owner_id ORDER BY b.CreatedAt DESC";
                $bres = $conn->query($bsql);
                if($bres->num_rows > 0) {
                    while($brow = $bres->fetch_assoc()) {
                        
                        $badgeClass = '';
                        if($brow['Status'] == 'Confirmed') $badgeClass = 'badge-success';
                        else if($brow['Status'] == 'Pending') $badgeClass = 'badge-warning';
                        else $badgeClass = 'badge-danger';
                        
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($brow['Location']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($brow['SeekerName']) . "</td>";
                        echo "<td>" . date('M j, Y', strtotime($brow['CreatedAt'])) . "</td>";
                        echo "<td><span class='badge $badgeClass'>" . htmlspecialchars($brow['Status']) . "</span></td>";
                        echo "<td>";
                        
                        if($brow['Status'] == 'Pending') {
                            echo "<a href='?verify_booking={$brow['BookingID']}' class='btn btn-success' style='padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-right:5px;'><i class='fa-solid fa-check'></i> Verify</a>";
                            echo "<a href='?reject_booking={$brow['BookingID']}' class='btn btn-danger' style='padding: 0.3rem 0.6rem; font-size: 0.8rem;' onclick='return confirm(\"Are you sure you want to reject this booking?\")'><i class='fa-solid fa-xmark'></i> Reject</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding: 2rem; color:var(--text-muted);'>No bookings received yet.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <div style="background:var(--white); border-radius:16px; box-shadow:var(--shadow); padding: 2rem; border: 1px solid var(--border);">
        <h3 style="margin-bottom:1.5rem; color:var(--secondary);"><i class="fa-solid fa-list"></i> Your Properties</h3>
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php
                $owner_id = $_SESSION['user_id'];
                $res = $conn->query("SELECT * FROM Properties WHERE OwnerID = $owner_id ORDER BY CreatedAt DESC");
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        
                        $badgeClass = '';
                        if($row['Status'] == 'Active') $badgeClass = 'badge-success';
                        else if($row['Status'] == 'Pending') $badgeClass = 'badge-warning';
                        else if($row['Status'] == 'Rejected') $badgeClass = 'badge-danger';
                        else $badgeClass = 'badge-info';
                        
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($row['Location']) . "</strong></td>";
                        echo "<td style='color:var(--primary); font-weight:600;'>₹" . number_format($row['Price']) . "</td>";
                        echo "<td><span class='badge $badgeClass'>" . htmlspecialchars($row['Status']) . "</span></td>";
                        echo "<td>";
                        
                        if($row['Status'] == 'Pending') {
                            // Check if payment done
                            $payCheck = $conn->query("SELECT PaymentID FROM Payments WHERE PropertyID={$row['PropertyID']}");
                            if($payCheck->num_rows == 0) {
                                echo "<a href='payment.php?id={$row['PropertyID']}' class='btn btn-secondary' style='padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-right:5px;'><i class='fa-solid fa-qrcode'></i> Pay fee</a>";
                            }
                        }
                        
                        echo "<a href='edit_property.php?id={$row['PropertyID']}' class='btn btn-primary' style='padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-right:5px;'><i class='fa-solid fa-pen'></i> Edit</a>";
                        echo "<a href='?delete={$row['PropertyID']}' class='btn btn-danger' style='padding: 0.3rem 0.6rem; font-size: 0.8rem;' onclick='return confirm(\"Are you sure you want to delete this property?\")'><i class='fa-solid fa-trash'></i> Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; padding: 3rem; color:var(--text-muted);'><i class='fa-solid fa-home' style='font-size:3rem; margin-bottom:1rem; opacity:0.5;'></i><br>You haven't listed any properties yet.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
