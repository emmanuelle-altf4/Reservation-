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
            <li><a href="adminmanage.php">STAFF DASHBOARD</a></li>
            <li><a href="pendings_customer.php">MANAGE SOMETHING HERE</a></li>
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
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $s): ?>
                <tr>
                    <td data-label="employee_number"><?php echo $s['employee_number']; ?></td>
                    <td data-label="employee_name"><?php echo htmlspecialchars($s['employee_name']); ?></td>
                     <td data-label="employee_name"><?php echo htmlspecialchars($s['employee_name']); ?></td>
                    <td data-label="Role"><?php echo $s['role']; ?></td>

                    <td>
                        <a href="update_staff.php?id=<?php echo $s['employee_number']; ?>">Edits</a> |
                        <a href="delete_staff.php?id=<?php echo $s['employee_number']; ?>" onclick="return confirm('Delete this staff account?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    
    </div>
    </body>
    </html>
