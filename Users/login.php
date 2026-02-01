<?php
ob_start();
session_start();

// DB connection
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

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_email = trim($_POST['customer_email'] ?? '');
    $input_password = $_POST['password'] ?? '';

    if ($customer_email === '' || $input_password === '') {
        $error = "Please fill in all fields.";
    } else {
        // Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM user WHERE customer_email = ?");
        $stmt->execute([$customer_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "No user found with that email.";
        } else {
            if (password_verify($input_password, $user['password_hash'])) {
                // Login success
                $_SESSION['user_id']        = $user['user_id'];
                $_SESSION['customer_name']  = $user['customer_name'];
                $_SESSION['customer_email'] = $user['customer_email'];

                header("Location: Welcome.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
ob_end_flush();
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
<div class="form">
    <form action="login.php" method="POST">
        <h1>Login</h1>
        <input type="email" name="customer_email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="loginbtn">Log In</button>

        <?php if (!empty($error)): ?>
            <p style="color:red; text-align:center;"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="registerbtn">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>
</body>
</html>

