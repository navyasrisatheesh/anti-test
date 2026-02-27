CREATE DATABASE IF NOT EXISTS pg_rental_db;
USE pg_rental_db;

CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('Seeker', 'Owner', 'Admin') NOT NULL,
    ContactInfo VARCHAR(20),
    Status ENUM('Pending', 'Approved', 'Blocked', 'Rejected') DEFAULT 'Approved',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Properties (
    PropertyID INT AUTO_INCREMENT PRIMARY KEY,
    OwnerID INT NOT NULL,
    Location VARCHAR(255) NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    Facilities TEXT,
    Status ENUM('Pending', 'Active', 'Inactive', 'Rejected') DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (OwnerID) REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE Reviews (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    PropertyID INT NOT NULL,
    UserID INT NOT NULL,
    Rating INT CHECK(Rating >= 1 AND Rating <= 5),
    Comment TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (PropertyID) REFERENCES Properties(PropertyID) ON DELETE CASCADE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE Notifications (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Message TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE Payments (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    OwnerID INT NOT NULL,
    PropertyID INT NOT NULL,
    Amount DECIMAL(10, 2) NOT NULL,
    Status ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (OwnerID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (PropertyID) REFERENCES Properties(PropertyID) ON DELETE CASCADE
);

-- Insert Default Admin
INSERT INTO Users (Name, Email, Password, Role, Status) VALUES 
('Admin', 'admin@stayly.com', 'admin123', 'Admin', 'Approved');
