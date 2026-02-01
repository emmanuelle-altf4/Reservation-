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


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $checkin_date  = $_POST['checkin'] ?? null;
    $checkout_date = $_POST['checkout'] ?? null;
    $room_type     = $_POST['room_type'] ?? null;
    $guests        = $_POST['guests'] ?? null;

 
    if ($checkin_date && $checkout_date && $room_type && $guests) {
        $stmt = $pdo->prepare("
            INSERT INTO customerreservation 
            (user_id, customer_name, checkin_date, checkout_date, room_type, guests, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['customer_name'],
            $checkin_date,
            $checkout_date,
            $room_type,
            $guests
        ]);

    
        header("Location: my_reservations.php");
        exit;
    } else {
        echo "Please fill in all fields.";
    }
}
?>
