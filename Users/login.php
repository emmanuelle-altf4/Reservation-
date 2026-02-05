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
    
    // DEBUG: Show what's being submitted
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px;'>";
    echo "<strong>DEBUG INFO:</strong><br>";
    echo "Email submitted: '$customer_email'<br>";
    echo "Password submitted: '$input_password'<br>";
    echo "Password length: " . strlen($input_password) . "<br>";
    echo "Password hex: " . bin2hex($input_password) . "<br>";
    echo "</div>";

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
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px;'>";
            echo "<strong>DATABASE INFO:</strong><br>";
            echo "User found: " . $user['customer_email'] . "<br>";
            echo "Stored hash: " . $user['password_hash'] . "<br>";
            echo "Hash length: " . strlen($user['password_hash']) . "<br>";
            echo "</div>";
            
            // Try multiple verification methods
            $password_trimmed = trim($input_password);
            $password_nl_removed = str_replace(["\r", "\n"], '', $input_password);
            
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px;'>";
            echo "<strong>VERIFICATION TESTS:</strong><br>";
            
            // Test 1: Normal verification
            $test1 = password_verify($input_password, $user['password_hash']);
            echo "Test 1 (raw): " . ($test1 ? '✓ PASS' : '✗ FAIL') . "<br>";
            
            // Test 2: Trimmed verification
            $test2 = password_verify($password_trimmed, $user['password_hash']);
            echo "Test 2 (trimmed): " . ($test2 ? '✓ PASS' : '✗ FAIL') . "<br>";
            
            // Test 3: Newline removed
            $test3 = password_verify($password_nl_removed, $user['password_hash']);
            echo "Test 3 (no newlines): " . ($test3 ? '✓ PASS' : '✗ FAIL') . "<br>";
            
            echo "</div>";
            
            if (password_verify($input_password, $user['password_hash']) ||
                password_verify(trim($input_password), $user['password_hash'])) {
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
    <!-- Add CSRF protection in production -->
</head>
<body>
<div class="form">
    <form action="login.php" method="POST" autocomplete="on">
        <h1>Login</h1>
        
        <div class="input-group">
            <label for="customer_email">Email</label>
            <input type="email" id="customer_email" name="customer_email" 
                   placeholder="Enter your email" 
                   value="<?php echo isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : ''; ?>"
                   required>
        </div>
        
        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" 
                   placeholder="Enter your password" 
                   required>
        </div>
        
        <button type="submit" class="loginbtn">Log In</button>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <div class="registerbtn">
            <p>Don't have an account? <a href="register.php">Register</a></p>
            <!-- comment ko muna -->
            <!-- <p><a href="forgot_password.php">Forgot Password?</a></p> -->
        </div>
    </form>
</div>

<script>
 
    document.querySelector('form').addEventListener('submit', function(e) {
        const email = document.getElementById('customer_email').value;
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            e.preventDefault();
            alert('Please fill in all fields.');
            return false;
        }
        
        if (!email.includes('@')) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            return false;
        }
    });
</script>
</body>
</html>
?>