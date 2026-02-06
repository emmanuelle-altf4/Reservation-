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
echo "<h2>Database Debug Information</h2>";
echo "<h3>1. Table Structure:</h3>";
$stmt = $pdo->query("DESCRIBE user");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($columns);
echo "</pre>";
echo "<h3>2. All Users in Database:</h3>";
$stmt = $pdo->query("SELECT user_id, customer_email, customer_name, password_hash, LENGTH(password_hash) as hash_length FROM user");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($users);
echo "</pre>";
echo "<h3>3. Test Password Verification:</h3>";
if (isset($_POST['test_email']) && isset($_POST['test_password'])) {
    $test_email = $_POST['test_email'];
    $test_password = $_POST['test_password'];
    
    $stmt = $pdo->prepare("SELECT password_hash FROM user WHERE customer_email = ?");
    $stmt->execute([$test_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found with email: $test_email<br>";
        echo "Stored hash: " . $user['password_hash'] . "<br>";
        echo "Hash length: " . strlen($user['password_hash']) . "<br>";
        
        if (password_verify($test_password, $user['password_hash'])) {
            echo "<span style='color: green; font-weight: bold;'> Password verification SUCCESSFUL</span><br>";
        } else {
            echo "<span style='color: red; font-weight: bold;'> Password verification FAILED</span><br>";
            
            if ($test_password === $user['password_hash']) {
                echo "<span style='color: orange;'>Note: Password matches hash directly (password might not be hashed in DB)</span><br>";
            }
        }
    } else {
        echo "No user found with email: $test_email<br>";
    }
}
echo "<h3>4. Create Test Hash:</h3>";
if (isset($_POST['password_to_hash'])) {
    $password = $_POST['password_to_hash'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Password: $password<br>";
    echo "Hash: $hash<br>";
    echo "Verify with password_verify('$password', '$hash'): " . 
         (password_verify($password, $hash) ? 'TRUE' : 'FALSE') . "<br>";
}
?>
<h3>Test Login:</h3>
<form method="POST">
    Email: <input type="email" name="test_email"><br>
    Password: <input type="password" name="test_password"><br>
    <input type="submit" value="Test Login">
</form>
<h3>Generate Hash:</h3>
<form method="POST">
    Password to hash: <input type="text" name="password_to_hash"><br>
    <input type="submit" value="Generate Hash">
</form>
<h3>Insert Test User (for testing):</h3>
<form method="POST" action="insert_test_user.php">
    <input type="hidden" name="action" value="insert_test">
    Email: <input type="email" name="email" value="test@example.com"><br>
    Password: <input type="text" name="password" value="password123"><br>
    Name: <input type="text" name="name" value="Test User"><br>
    <input type="submit" value="Insert Test User">
</form>