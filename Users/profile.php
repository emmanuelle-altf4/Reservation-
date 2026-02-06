<?php
session_start();
if (!isset($_SESSION['customer_name'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Veripool Reservation</title>
    <link rel="stylesheet" href="css/dash.css">
</head>
<body>
<div class="navbar">
    <a href="dashboard.php" class="logo">Villianueva</a>
    <ul>
        <li><a href="make_reservation.php">Make Reservation</a></li>
        <li><a href="my_reservations.php">My Reservations</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard-container">
    <div class="property-details">
        <h2>Profile</h2>
        <ul>
            <li>Full Name:<?php echo htmlspecialchars($_SESSION['customer_name']); ?></li>
            <li>Email: <?php echo htmlspecialchars($_SESSION['customer_email']); ?></li>
            <button class="">Edit</button> <button class="">Save</button>
        </ul>
    </div>
</div>
</body>
</html>
