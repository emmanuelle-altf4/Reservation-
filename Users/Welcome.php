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
        <li><a href="reservation_dashboard.php">Make Reservation</a></li>
        <li><a href="my_reservations.php">My Reservations</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard-container">
    <div class="property-details">
        <h2>Deluxe Two-Bedroom Villa</h2>
        <p><strong>Ernesto Villa</strong> — sleeps 5 guests</p>
        <ul>
            <li>Room 1 (aircon room): 1 King bed</li>
            <li>Room 2 (aircon room): 1 Queen bed</li>
            <li>Room 3 (aircon room): 1 Single bed</li>
             <li>Room 4 (aircon room): 1 Single bed</li>
            <li>2.5 Bathrooms</li>
            <li>260 m² area</li>
            <li>Garden view</li>
            <li>2 Private swimming pool</li>
            <li>Wireless Internet</li>
            <li>Weekly Room Service</li>
            <li>Towels & Television</li>
        </ul>
    </div>
    <div class="property-details">
        <h2>Standard Villa</h2>
        <p><strong>Pavillion villa</strong> — sleeps 5 guests</p>
        <ul>
            <li>Room 1: 1 King bed</li>
            <li>Room 2: 1 Queen bed</li>
            <li>Room 3: 1 Single bed</li>
            <li>2.5 Bathrooms</li>
            <li>Private swimming pool</li>
            <li>Wireless Internet</li>
            <li>Weekly Room Service</li>
        </ul>
    </div>
    <!-- <div class="booking-panel">
        <h3>Book Your Stay</h3>
        <form action="process_reservation.php" method="POST">
            <label>Check-in Date:</label>
            <input type="date" name="checkin" required>

            <label>Check-out Date:</label>
            <input type="date" name="checkout" required>

            <label>Guests:</label>
            <input type="number" name="guests" min="1" max="5" required>

            <button type="submit" class="loginbtn">Book Now</button>
        </form>
    </div> -->
</div>
</body>
</html>
