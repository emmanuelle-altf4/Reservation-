<?php
session_start();
if (!isset($_SESSION['employee_number']) || $_SESSION['role'] !== 'Admin') { 
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

$staff_id = $_GET['id'] ?? 0;

// Prevent deleting yourself
if ($staff_id == $_SESSION['employee_number']) {
    die("You cannot delete your own account.");
}

try {
    $stmt = $pdo->prepare("DELETE FROM staff WHERE employee_number = ?");
    $stmt->execute([$staff_id]);
    
    $_SESSION['success_message'] = "Staff member deleted successfully!";
    header("Location: manage_staff.php");
    exit;
} catch (Exception $e) {
    die("Error deleting staff: " . $e->getMessage());
}
?>