<?php
require_once 'db.php';
$conn->query("CREATE TABLE IF NOT EXISTS Bookings (
    BookingID INT AUTO_INCREMENT PRIMARY KEY,
    PropertyID INT NOT NULL,
    UserID INT NOT NULL,
    OwnerID INT NOT NULL,
    Status ENUM('Pending', 'Confirmed', 'Rejected') DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (PropertyID) REFERENCES Properties(PropertyID) ON DELETE CASCADE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (OwnerID) REFERENCES Users(UserID) ON DELETE CASCADE
)");
echo "Done";
?>
