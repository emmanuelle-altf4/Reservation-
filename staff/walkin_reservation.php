<?php
// connect to staff conn
session_start();
if (!isset($_SESSION['employee_number'])) {
    header("Location: login.php");
    exit;
}

// connection to database
$host = "localhost";
$dbname = "resortreservation";
$username = "root";
$password = "";



try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}


// select check in and out to insert in database

$stmt = $pdo->query("SELECT checkin_date, checkout_date FROM customerreservation WHERE status='Confirmed'");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Dashboard</title>
    <link rel="stylesheet" href="../Users/css/dash.css">
    <style>
  
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 2rem;
            background: #fff;
            border: 1px solid #ddd;
            padding: 1rem;
            border-radius: 6px;
        }
        .calendar div {
            padding: 0.75rem;
            text-align: center;
            border-radius: 4px;
        }
        .calendar .reserved {
            background-color: #ffcccc;
            color: #333;
            font-weight: bold;
        }
        .calendar .today {
            background-color: #cce5ff;
            font-weight: bold;
        }
    </style>
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
        <p><strong>Pavillion Villa</strong> — sleeps 5 guests</p>
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


    <div class="booking-panel">
        <h3>Book Your Stay</h3>
        <!-- method is POST to save data to be sent to the OTP EMAIL -->
        <form action="../Users/process_reservation.php" method="POST"> 
            <label>Villa Type:</label>
            <select name="room_type" required>
                <option value="Deluxe Two-Bedroom Villa">Deluxe Two-Bedroom Villa</option>
                <option value="Standard Villa">Standard Villa</option>
            </select>

            <label>Check-in Date:</label>
            <input type="date" name="checkin" required>

            <label>Check-out Date:</label>
            <input type="date" name="checkout" required>

            <label>Guests:</label>
            <input type="number" name="guests" min="1" max="5" required>

            <button type="submit" class="loginbtn">Reserve</button>
        </form>
    </div>
</div>
</div>
</body>
</html>
