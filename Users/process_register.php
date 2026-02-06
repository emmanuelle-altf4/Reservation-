<?php
session_start();


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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $plain_password = $_POST['password'];

    if ($customer_name && $customer_email && $plain_password) {
    
        $stmt = $pdo->prepare("SELECT * FROM user WHERE customer_email = ?");
        $stmt->execute([$customer_email]);
        if ($stmt->fetch()) {
            echo "Email already registered. Please log in.";
            exit;
        }

        // Hash password securely
        $password_hash = password_hash($plain_password, PASSWORD_BCRYPT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO user (customer_name, customer_email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$customer_name, $customer_email, $password_hash]);

        echo "Registration successful! You can now <a href='login.php'>log in</a>.";
    } else {
        echo "Please fill in all fields.";
    }
}
?>
