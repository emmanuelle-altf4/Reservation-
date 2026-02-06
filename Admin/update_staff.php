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

// Get staff details
$stmt = $pdo->prepare("SELECT * FROM staff WHERE employee_number = ?");
$stmt->execute([$staff_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    die("Staff member not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_name = trim($_POST['employee_name']);
    $role = $_POST['role'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update staff
    $update_data = [$employee_name, $role, $staff_id];
    
    // If password is provided, update it too
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            die("Passwords do not match.");
        }
        if (strlen($new_password) < 6) {
            die("Password must be at least 6 characters long.");
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE staff SET employee_name = ?, password_hash = ?, role = ? WHERE employee_number = ?";
        $update_data = [$employee_name, $hashed_password, $role, $staff_id];
    } else {
        $update_query = "UPDATE staff SET employee_name = ?, role = ? WHERE employee_number = ?";
    }
    
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->execute($update_data);
    
    $_SESSION['success_message'] = "Staff member updated successfully!";
    header("Location: adminmanage.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Staff</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        .container { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input, select { width: 100%; padding: 0.5rem; }
        .btn { padding: 0.5rem 1rem; background: #007BFF; color: white; border: none; cursor: pointer; }
        .back-link { display: inline-block; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Staff: <?php echo htmlspecialchars($staff['employee_name']); ?></h1>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="employee_name" value="<?php echo htmlspecialchars($staff['employee_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="Employee" <?php echo $staff['role'] == 'Employee' ? 'selected' : ''; ?>>Staff</option>
                    <option value="Admin" <?php echo $staff['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <!-- leave if not in use -->
            <div class="form-group">
                <label>New Password (leave blank to keep current):</label>
                <input type="password" name="new_password">
            </div>
            
            <div class="form-group">
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password">
            </div>
            
            <button type="submit" class="btn">Update Staff</button>
            <a href="manage_staff.php" class="back-link">Cancel</a>
        </form>
    </div>
</body>
</html>