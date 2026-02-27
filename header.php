<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stayly | Premium Rental Accommodations</title>
    <meta name="description" content="Find the best PGs and rental properties directly from owners with zero brokerage.">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <a href="index.php" class="logo"><i class="fa-solid fa-house-chimney"></i> Stay<span>ly</span></a>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="search_results.php">Search</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] == 'Seeker'): ?>
                    <li><a href="dashboard_user.php"><i class="fa-solid fa-user"></i> Dashboard</a></li>
                <?php elseif($_SESSION['role'] == 'Owner'): ?>
                    <li><a href="dashboard_owner.php"><i class="fa-solid fa-building"></i> Dashboard</a></li>
                <?php elseif($_SESSION['role'] == 'Admin'): ?>
                    <li><a href="dashboard_admin.php"><i class="fa-solid fa-lock"></i> Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn btn-secondary"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn btn-secondary">Login</a></li>
                <li><a href="register.php" class="btn btn-primary">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
