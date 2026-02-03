<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
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
    // ✅ Match the form field name
    $employee_name = $_POST['employee_name'] ?? null;
    $password      = $_POST['password'] ?? null;
    $role          = $_POST['role'] ?? 'Staff';

    if ($employee_name && $password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // ✅ Only insert into the columns that exist
        $stmt = $pdo->prepare("
            INSERT INTO staff (employee_name, password_hash, role)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$employee_name, $password_hash, $role]);

        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Please fill in all fields.";
    }
}
?>
