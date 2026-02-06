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

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Reservation ID missing.");
}

// Fetch reservation
$stmt = $pdo->prepare("SELECT * FROM customerreservation WHERE reservation_id = ?");
$stmt->execute([$id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    die("Reservation not found.");
}

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $checkin_date  = $_POST['checkin'];
    $checkout_date = $_POST['checkout'];
    $room_type     = $_POST['room_type'];
    $guests        = $_POST['guests'];
    $status        = $_POST['status'];

    $stmt = $pdo->prepare("
        UPDATE customerreservation 
        SET checkin_date=?, checkout_date=?, room_type=?, guests=?, status=?, updated_at=NOW()
        WHERE reservation_id=?
    ");
    $stmt->execute([$checkin_date, $checkout_date, $room_type, $guests, $status, $id]);

    header("Location: manage_reservations.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Reservation</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="form">
    <h1>Edit Reservation #<?php echo $reservation['reservation_id']; ?></h1>
    <form method="POST">
        <label>Check-in Date:</label>
        <input type="date" name="checkin" value="<?php echo $reservation['checkin_date']; ?>" required>

        <label>Check-out Date:</label>
        <input type="date" name="checkout" value="<?php echo $reservation['checkout_date']; ?>" required>

        <label>Room Type:</label>
        <input type="text" name="room_type" value="<?php echo htmlspecialchars($reservation['room_type']); ?>" required>

        <label>Guests:</label>
        <input type="number" name="guests" value="<?php echo $reservation['guests']; ?>" required>

        <label>Status:</label>
        <select name="status" required>
            <option value="Pending" <?php if($reservation['status']=="Pending") echo "selected"; ?>>Pending</option>
            <option value="Confirmed" <?php if($reservation['status']=="Confirmed") echo "selected"; ?>>Confirmed</option>
            <option value="Cancelled" <?php if($reservation['status']=="Cancelled") echo "selected"; ?>>Cancelled</option>
        </select>

        <button type="submit" class="loginbtn">Update Reservation</button>
    </form>
</div>
</body>
</html>
