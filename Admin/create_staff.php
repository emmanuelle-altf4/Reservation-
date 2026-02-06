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


try {
    $check_enum = $pdo->query("SHOW COLUMNS FROM staff LIKE 'role'");
    $column_info = $check_enum->fetch(PDO::FETCH_ASSOC);
    
    // Extract ENUM values from the Type field
    $type = $column_info['Type'];
    preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
    $allowed_roles = explode("','", $matches[1]);
    
   
    if (empty($allowed_roles)) {
        $allowed_roles = ['Admin', 'Employee'];
    }
    
} catch (Exception $e) {

    $allowed_roles = ['Admin', 'Employee'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $required_fields = ['employee_name', 'password', 'confirm_password', 'role'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            die("Error: All fields are required. Missing: $field");
        }
    }
    
    $employee_name = trim($_POST['employee_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validate inputs
    if (empty($employee_name) || empty($password) || empty($confirm_password) || empty($role)) {
        die("Error: All fields are required.");
    }
    
    if ($password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }
    
    if (strlen($password) < 6) {
        die("Error: Password must be at least 6 characters long.");
    }
    

    if (!in_array($role, $allowed_roles)) {
        die("Error: Invalid role selected. Allowed roles: " . implode(', ', $allowed_roles));
    }
    

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE employee_name = ?");
        $check_stmt->execute([$employee_name]);
        $exists = $check_stmt->fetchColumn();
        
        if ($exists > 0) {
            die("Error: Staff member with this name already exists.");
        }

        $insert_stmt = $pdo->prepare("INSERT INTO staff (employee_name, password_hash, role) VALUES (?, ?, ?)");
        $insert_stmt->execute([$employee_name, $hashed_password, $role]);
        

        $new_staff_id = $pdo->lastInsertId();
        
        $_SESSION['success_message'] = "Staff member '$employee_name' added successfully with ID: $new_staff_id";
        header("Location: adminmanage.php");
        exit;
        
    } catch (Exception $e) {
        die("Error creating staff: " . $e->getMessage() . "<br>Allowed roles: " . implode(', ', $allowed_roles));
    }
} else {
    //
    header("Location: adminmanage.php");
    exit;
}
?>