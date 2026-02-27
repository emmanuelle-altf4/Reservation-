<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['employee_number']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
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
    $_SESSION['error'] = "DB Connection failed: " . $e->getMessage();
    header("Location: adminmanage.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No staff ID provided.";
    header("Location: adminmanage.php");
    exit;
}

$staff_id = $_GET['id'];

// Get staff details
try {
    $stmt = $pdo->prepare("SELECT employee_number, employee_name, role FROM staff WHERE employee_number = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        $_SESSION['error'] = "Staff member not found.";
        header("Location: adminmanage.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching staff: " . $e->getMessage();
    header("Location: adminmanage.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_name = trim($_POST['employee_name']);
    $role = $_POST['role'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($employee_name)) {
        $errors[] = "Employee name is required.";
    }
    
    // Validate role (only Admin or Employee allowed based on your table)
    $allowed_roles = ['Admin', 'Employee'];
    if (!in_array($role, $allowed_roles)) {
        $errors[] = "Invalid role selected. Please choose Admin or Employee.";
    }
    
    // Check if trying to update own account
    $is_own_account = ($staff_id == $_SESSION['employee_number']);
    
    // If password is provided, validate it
    $update_password = !empty($new_password);
    if ($update_password) {
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['error'] = implode(" ", $errors);
        header("Location: update_staff.php?id=" . $staff_id);
        exit;
    }
    
    try {
        // Check if employee_name already exists (excluding current staff)
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE employee_name = ? AND employee_number != ?");
        $check_stmt->execute([$employee_name, $staff_id]);
        if ($check_stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Error: Staff member with name '$employee_name' already exists.";
            header("Location: update_staff.php?id=" . $staff_id);
            exit;
        }
        
        // Update staff based on whether password is provided
        if ($update_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE staff SET employee_name = ?, password_hash = ?, role = ? WHERE employee_number = ?";
            $update_data = [$employee_name, $hashed_password, $role, $staff_id];
        } else {
            $update_query = "UPDATE staff SET employee_name = ?, role = ? WHERE employee_number = ?";
            $update_data = [$employee_name, $role, $staff_id];
        }
        
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute($update_data);
        
        // If updating own account, update session variables
        if ($is_own_account) {
            $_SESSION['employee_name'] = $employee_name;
            $_SESSION['role'] = $role;
        }
        
        $_SESSION['success'] = "Staff member '" . htmlspecialchars($employee_name) . "' updated successfully!";
        header("Location: adminmanage.php");
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating staff: " . $e->getMessage();
        header("Location: update_staff.php?id=" . $staff_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff - <?php echo htmlspecialchars($staff['employee_name']); ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: white;
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
        }
        .container { 
            max-width: 500px; 
            margin: 0 auto; 
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #333;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        .form-group { 
            margin-bottom: 1.5rem; 
        }
        label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: bold;
            color: #555;
        }
        input, select { 
            width: 100%; 
            padding: 0.75rem; 
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102,126,234,0.2);
        }
        .btn { 
            padding: 0.75rem 1.5rem; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            border: none; 
            border-radius: 5px;
            cursor: pointer; 
            font-size: 1rem;
            font-weight: bold;
            transition: opacity 0.3s;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .btn-secondary {
            background: #6c757d;
            margin-left: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .note {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .own-account-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($staff_id == $_SESSION['employee_number']): ?>
            <div class="own-account-warning">
                worneng You are editing your own account. Changes will take effect immediately.
            </div>
        <?php endif; ?>

        <h1>Edit Staff: <?php echo htmlspecialchars($staff['employee_name']); ?></h1>
        
        <form method="POST">
            <div class="form-group">
                <label>Employee Name:</label>
                <input type="text" 
                       name="employee_name" 
                       value="<?php echo htmlspecialchars($staff['employee_name']); ?>" 
                       required
                       placeholder="Enter employee name">
            </div>
            
            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="Admin" <?php echo $staff['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="Employee" <?php echo $staff['role'] == 'Employee' ? 'selected' : ''; ?>>Employee</option>
                </select>
                <div class="note">Based on your database, only Admin and Employee roles are available.</div>
            </div>
            
            <div class="form-group">
                <label>New Password (leave blank to keep current):</label>
                <input type="password" 
                       name="new_password" 
                       placeholder="Enter new password (min. 6 characters)"
                       minlength="6">
                <div class="note">Only fill this if you want to change the password</div>
            </div>
            
            <div class="form-group">
                <label>Confirm New Password:</label>
                <input type="password" 
                       name="confirm_password" 
                       placeholder="Confirm new password">
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button type="submit" class="btn">Update Staff</button>
                <a href="adminmanage.php" class="back-link">← Back to Staff Management</a>
            </div>
        </form>
    </div>

    <script>
        // Client-side password validation
        document.querySelector('form').addEventListener('submit', function(e) {
            var newPass = document.querySelector('input[name="new_password"]').value;
            var confirmPass = document.querySelector('input[name="confirm_password"]').value;
            
            // Only validate if password field is not empty
            if (newPass !== '') {
                if (newPass.length < 6) {
                    alert('Password must be at least 6 characters long!');
                    e.preventDefault();
                    return false;
                }
                
                if (newPass !== confirmPass) {
                    alert('Passwords do not match!');
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>