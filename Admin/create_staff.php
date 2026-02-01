<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
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
    $full_name = $_POST['employee_number'];
    $username  = $_POST['employee_name'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO staff (employee_number, employee_name, password, ) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $username, $email, $password]);

    header("Location: admin_dashboard.php");
    exit;
}
?>
