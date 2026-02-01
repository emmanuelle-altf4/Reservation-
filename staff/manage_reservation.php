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

$stmt = $pdo->query("SELECT * FROM customerreservation ORDER BY created_at DESC");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations</title>
     <!-- <link rel="stylesheet" href="css/manage_reservationstyle.css"> -->
<style>
    /* My CSS is AI */
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

.form h1 {
  margin-bottom: 1rem;
  color: #333;
}

.form p {
  margin-bottom: 1rem;
  color: #555;
}


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


.reservation-table td a {
  color: #007BFF;
  text-decoration: none;
  margin: 0 0.25rem;
}

.reservation-table td a:hover {
  text-decoration: underline;
}

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
    <a href="dashboard.php" class="logo">Resort Staff Panel</a>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="manage_reservations.php">Manage Reservations</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form">
    <h1>Manage Reservations</h1>
    <p>All customer reservations are listed below. Staff can edit or delete entries.</p>

    <table class="reservation-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Room Type</th>
                <th>Guests</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $res): ?>
            <tr>
                <td data-label="ID"><?php echo $res['reservation_id']; ?></td>
                <td data-label="Customer"><?php echo htmlspecialchars($res['customer_name']); ?></td>
                <td data-label="Check-in"><?php echo $res['checkin_date']; ?></td>
                <td data-label="Check-out"><?php echo $res['checkout_date']; ?></td>
                <td data-label="Room Type"><?php echo $res['room_type']; ?></td>
                <td data-label="Guests"><?php echo $res['guests']; ?></td>
                <td data-label="Status"><?php echo $res['status']; ?></td>
                <td data-label="Created"><?php echo $res['created_at']; ?></td>
                <td>
                    <a href="update_reservation.php?id=<?php echo $res['reservation_id']; ?>">Edit</a> |
                    <a href="delete_reservation.php?id=<?php echo $res['reservation_id']; ?>" onclick="return confirm('Delete this reservation?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
