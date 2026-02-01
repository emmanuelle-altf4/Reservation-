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

// Delete reservation
$stmt = $pdo->prepare("DELETE FROM customerreservation WHERE reservation_id = ?");
$stmt->execute([$id]);

header("Location: manage_reservation.php");
exit;
?>
