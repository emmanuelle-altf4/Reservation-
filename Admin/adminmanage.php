<?php
session_start();
if (!isset($_SESSION['employee_number'])) 
    { header("Location: login.php"); exit; 
}
if ($_SESSION['role'] !== 'Admin') 
    { header("Location: staff_dashboard.php"); exit; 
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


$stmt = $pdo->query("SELECT * FROM staff ORDER BY employee_number DESC");
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admincss.css">
</head>
<body>
<div class="navbar">
    <a href="admin_dashboard.php" class="logo">Admin Panel</a>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="manage_users.php">User Dashboard</a></li>
        <li><a href="../staff/dashboard.php">Reservationconfirmation </a></li>
          <li><a href="walkin_reservation.php"> Walk in lang i20 </a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['employee_name']); ?>!</h1>
    <p>Manage staff accounts below:</p>

    <table class="reservation-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff as $s): ?>
            <tr>
                <td data-label="employee_number"><?php echo $s['employee_number']; ?></td>
                <td data-label="employee_name"><?php echo htmlspecialchars($s['employee_name']); ?></td>
                <td data-label="Role"><?php echo $s['role']; ?></td>
                <td>
                    <a href="update_staff.php?id=<?php echo $s['employee_number']; ?>">Edit</a> |
                    <a href="delete_staff.php?id=<?php echo $s['employee_number']; ?>" onclick="return confirm('Delete this staff account?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- try lang if gumana G -->
<h2>Add New Staff</h2>
<form action="create_staff.php" method="POST">
    <label>Full Name:</label>
    <input type="text" name="employee_name" required placeholder="Enter full name">

    <label>Password:</label>
    <input type="password" name="password" required placeholder="Enter password">

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password" required placeholder="Confirm password">

    <label>Role:</label>
    <select name="role" required>
    <option value="">Select Role</option>
    <option value="Admin">Admin</option>
    <option value="Employee">Employee</option>
</select>

    <button type="submit" class="loginbtn">Add Staff</button>
</form>
</form>


</div>
</body>
</html>
