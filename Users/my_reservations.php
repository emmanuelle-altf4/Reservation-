<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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

$stmt = $pdo->prepare("SELECT * FROM customerreservation WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reservations</title>
    <link rel="stylesheet" href="css/dash.css">
</head>
<body>

<div class="navbar">
    <a href="Welcome.php" class="logo">Villianueva</a>
    <ul>
        <li><a href="reservation_dashboard.php">Make Reservation</a></li>
        <li><a href="my_reservations.php">My Reservations</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form">
    <table class="reservation-table">
    <thead>
        <tr>
            <th>Reservation ID</th>
            <th>Customer Name</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Room Type</th>
            <th>Guests</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservations as $res): ?>
        <tr>
            <td data-label="Reservation ID"><?php echo $res['reservation_id']; ?></td>
            <td data-label="Customer Name"><?php echo htmlspecialchars($res['customer_name']); ?></td>
            <td data-label="Check-in"><?php echo $res['checkin_date']; ?></td>
            <td data-label="Check-out"><?php echo $res['checkout_date']; ?></td>
            <td data-label="Room Type"><?php echo $res['room_type']; ?></td>
            <td data-label="Guests"><?php echo $res['guests']; ?></td>
            <td data-label="Status"><?php echo $res['status']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
</body>
</html>
