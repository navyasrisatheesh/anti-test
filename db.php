<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // default XAMPP root password
$dbname = 'pg_rental_db';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Ensure session starts on every page automatically if included
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}
  