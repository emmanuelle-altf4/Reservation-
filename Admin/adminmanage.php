<?php
session_start();
if (!isset($_SESSION['employee_number'])) { 
    header("Location: login.php"); 
    exit; 
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: staff_dashboard.php"); 
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

// Fetch all staff
$stmt = $pdo->query("SELECT employee_number, employee_name, role FROM staff ORDER BY employee_number DESC");
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Manage Staff</title>
    <link rel="stylesheet" href="css/admincss.css">
    <style>
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .loginbtn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .loginbtn:hover {
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            margin: 0 2px;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        .role-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .role-admin {
            background-color: #ffd700;
            color: #000;
        }
        .role-employee {
            background-color: #87ceeb;
            color: #000;
        }
    </style>
</head>
<body>
<div class="navbar">
    <a href="admin_dashboard.php" class="logo">Admin Panel</a>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="adminmanage.php">User Dashboard</a></li>
        <li><a href="adminmanage.php" class="active">Staff Dashboard</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form" style="padding: 20px; margin-top: 60px;">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['employee_name'] ?? 'Admin'); ?>! (Staff Management)</h1>
    
    <!-- Display messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert-success">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-error">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <h2>Current Staff Members</h2>
    
    <?php if (count($staff) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Employee #</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['employee_number']); ?></td>
                <td><?php echo htmlspecialchars($s['employee_name']); ?></td>
                <td>
                    <span class="role-badge role-<?php echo strtolower($s['role']); ?>">
                        <?php echo htmlspecialchars($s['role']); ?>
                    </span>
                </td>
                <td>
                    <a href="update_staff.php?id=<?php echo $s['employee_number']; ?>" class="edit-btn">Edit</a>
                    <a href="delete_staff.php?id=<?php echo $s['employee_number']; ?>" 
                       class="delete-btn" 
                       onclick="return confirm('Are you sure you want to delete this staff account?\nEmployee: <?php echo htmlspecialchars($s['employee_name']); ?>')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No staff members found.</p>
    <?php endif; ?>

    <!-- Add New Staff Form -->
    <h2>Add New Staff</h2>
    <form action="create_staff.php" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label>Employee Name:</label>
            <input type="text" name="employee_name" required 
                   placeholder="Enter employee name">
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required 
                   placeholder="Enter password" 
                   minlength="6" 
                   id="password">
        </div>

        <div class="form-group">
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required 
                   placeholder="Confirm password" 
                   id="confirm_password">
        </div>

        <div class="form-group">
            <label>Role:</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="Admin">Admin</option>
                <option value="Employee">Employee</option>
            </select>
        </div>

        <button type="submit" class="loginbtn">Add Staff</button>
    </form>
</div>

<script>
function validateForm() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    
    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
    }
    
    if (password.length < 6) {
        alert("Password must be at least 6 characters long!");
        return false;
    }
    
    return true;
}
</script>

</body>
</html>