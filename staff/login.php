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
    $employee_name = trim($_POST['employee_name'] ?? '');
    $input_password = $_POST['password'] ?? '';

    if ($employee_name === '' || $input_password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE employee_name = ?");
        $stmt->execute([$employee_name]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$staff) {
            $error = "No user found with that name.";
        } else {
            if (password_verify($input_password, $staff['password_hash'])) {
                // Login success
                $_SESSION['employee_number'] = $staff['employee_number'];
                $_SESSION['employee_name']   = $staff['employee_name'];
                $_SESSION['role']            = $staff['role']; // NEW

                // Redirect based on role
                if ($staff['role'] === 'Admin') {
                    header("Location: ../Admin/admin_dashboard.php");
                } else {
                    header("Location: ../staff/dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Log in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="form">
    <form action="login.php" method="POST">
        <h1>Login</h1>
        <?php if ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <input type="text" name="employee_name" placeholder="Employee Name" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="loginbtn">Log In</button>
    </form>
</div>
</body>
</html>
