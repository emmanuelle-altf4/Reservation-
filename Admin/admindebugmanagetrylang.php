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

$error = '';
$success = '';
$employee_name = '';
$role = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_name = trim($_POST['employee_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Validate inputs
    if (empty($employee_name) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if staff with same name already exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE employee_name = ?");
        $check_stmt->execute([$employee_name]);
        $exists = $check_stmt->fetchColumn();
        
        if ($exists > 0) {
            $error = "Staff member with this name already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                // Insert new staff
                $insert_stmt = $pdo->prepare("INSERT INTO staff (employee_name, password_hash, role) VALUES (?, ?, ?)");
                $insert_stmt->execute([$employee_name, $hashed_password, $role]);
                
                $new_staff_id = $pdo->lastInsertId();
                $success = "Staff member '$employee_name' added successfully with ID: $new_staff_id";
                
                // Clear form
                $employee_name = '';
                $role = '';
                
            } catch (Exception $e) {
                $error = "Error creating staff: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Staff</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
            padding: 30px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 500;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            color: #6c757d;
        }
        
        .password-match {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .match {
            color: #28a745;
        }
        
        .no-match {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="manage_staff.php" class="back-link">← Back to Staff Management</a>
        <h1>Add New Staff Member</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="staffForm">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="employee_name" 
                       value="<?php echo htmlspecialchars($employee_name); ?>" 
                       required placeholder="Enter full name">
            </div>
            
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" id="password" 
                       required placeholder="Enter password (min 6 characters)">
                <div class="password-strength" id="password-strength">
                    Password strength: <span id="strength-text">None</span>
                </div>
            </div>
            
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       required placeholder="Confirm password">
                <div class="password-match" id="password-match">
                    <!-- Will show if passwords match -->
                </div>
            </div>
            
            <div class="form-group">
                <label>Role *</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="Staff" <?php echo $role == 'Staff' ? 'selected' : ''; ?>>Staff</option>
                    <option value="Manager" <?php echo $role == 'Manager' ? 'selected' : ''; ?>>Manager</option>
                    <option value="Admin" <?php echo $role == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Add Staff Member</button>
        </form>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const strengthText = document.getElementById('strength-text');
        const matchDiv = document.getElementById('password-match');
        
        // Password strength checker
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 'None';
            let color = '#6c757d';
            
            if (password.length === 0) {
                strength = 'None';
                color = '#6c757d';
            } else if (password.length < 6) {
                strength = 'Weak';
                color = '#dc3545';
            } else if (password.length < 10) {
                strength = 'Medium';
                color = '#ffc107';
            } else {
                strength = 'Strong';
                color = '#28a745';
            }
            
            strengthText.textContent = strength;
            strengthText.style.color = color;
        });
        
        // Password match checker
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (confirm.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirm) {
                matchDiv.innerHTML = '<span class="match"><i class="fas fa-check"></i> Passwords match</span>';
            } else {
                matchDiv.innerHTML = '<span class="no-match"><i class="fas fa-times"></i> Passwords do not match</span>';
            }
        }
        
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmInput.addEventListener('input', checkPasswordMatch);
        
        // Form validation
        document.getElementById('staffForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const role = document.querySelector('select[name="role"]').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                passwordInput.focus();
                return false;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match');
                confirmInput.focus();
                return false;
            }
            
            if (!role) {
                e.preventDefault();
                alert('Please select a role');
                return false;
            }
        });
    });
    </script>
</body>
</html>