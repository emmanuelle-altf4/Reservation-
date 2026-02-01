<?php
session_start();
if (!isset($_SESSION['employee_name'])) {
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


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_name = $_POST['customer_name'];
    $checkin_date  = $_POST['checkin'];
    $checkout_date = $_POST['checkout'];
    $room_type     = $_POST['room_type'];
    $guests        = $_POST['guests'];

    $stmt = $pdo->prepare("
        INSERT INTO customerreservation 
        (user_id, customer_name, checkin_date, checkout_date, room_type, guests, status) 
        VALUES (NULL, ?, ?, ?, ?, ?, 'Confirmed')
    ");
    $stmt->execute([$customer_name, $checkin_date, $checkout_date, $room_type, $guests]);

    header("Location: manage_reservation.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Walk-in Reservation</title>
    <!-- <link rel="stylesheet" href="css/style.css"> -->

      <style>
   /* Navbar styling */
.navbar {
  background-color: #fff;
  border-bottom: 1px solid #ddd;
  padding: 0.75rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: Arial, sans-serif;
}

.navbar .logo {
  font-size: 1.2rem;
  font-weight: bold;
  color: #333;
  text-decoration: none;
}

.navbar ul {
  list-style: none;
  display: flex;
  gap: 1.5rem;
  margin: 0;
  padding: 0;
}

.navbar ul li a {
  text-decoration: none;
  color: #333;
  font-size: 0.95rem;
  transition: color 0.2s ease;
}

.navbar ul li a:hover {
  color: #007BFF;
}

/* Dashboard form container */
.form {
  max-width: 1100px;
  margin: 2rem auto;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 2rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.form h1 {
  margin-top: 0;
  color: #333;
}

.form p {
  color: #555;
  margin-bottom: 1rem;
}

/* Reservation table styling */
.reservation-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 6px;
  overflow: hidden;
}

.reservation-table th {
  background: #f5f5f5;
  color: #333;
  text-align: left;
  padding: 0.75rem 1rem;
  font-weight: bold;
  border-bottom: 1px solid #ddd;
}

.reservation-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eee;
  color: #555;
}

.reservation-table tr:nth-child(even) {
  background: #fafafa;
}

.reservation-table tr:hover {
  background: #f0f8ff;
}

/* Action links */
.reservation-table td a {
  color: #007BFF;
  text-decoration: none;
  margin: 0 0.25rem;
}

.reservation-table td a:hover {
  text-decoration: underline;
}

/* Responsive table for mobile */
@media (max-width: 768px) {
  .reservation-table,
  .reservation-table thead,
  .reservation-table tbody,
  .reservation-table th,
  .reservation-table td,
  .reservation-table tr {
    display: block;
    width: 100%;
  }

  .reservation-table tr {
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 0.5rem;
  }

  .reservation-table td {
    border: none;
    padding: 0.5rem;
    position: relative;
  }

  .reservation-table td::before {
    content: attr(data-label);
    font-weight: bold;
    display: block;
    margin-bottom: 0.25rem;
    color: #333;
  }
}
</style>
</head>
<body>
<div class="navbar">
    <a href="staff_dashboard.php" class="logo">Resort Staff Panel</a>
    <ul>
        <li><a href="staff_dashboard.php">Dashboard</a></li>
        <li><a href="manage_reservations.php">Manage Reservations</a></li>
        <li><a href="walkin_reservation.php">Walk-in Reservation</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form">
    <h1>Walk-in Customer Reservation</h1>
    <form method="POST">
        <label>Customer Name:</label>
        <input type="text" name="customer_name" required>

        <label>Check-in Date:</label>
        <input type="date" name="checkin" required>

        <label>Check-out Date:</label>
        <input type="date" name="checkout" required>

        <label>Room Type:</label>
        <select name="room_type" required>
            <option value="Deluxe Two-Bedroom Villa">Deluxe Two-Bedroom Villa</option>
            <option value="Standard Villa">Standard Villa</option>
        </select>

        <label>Guests:</label>
        <input type="number" name="guests" min="1" max="10" required>

        <button type="submit" class="loginbtn">Add Reservation</button>
    </form>
</div>
</body>
</html>
