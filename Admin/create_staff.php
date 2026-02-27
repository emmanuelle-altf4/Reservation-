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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['employee_name', 'password', 'confirm_password', 'role'];
    
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $_SESSION['error'] = "Error: All fields are required. Missing: " . implode(', ', $missing_fields);
        header("Location: adminmanage.php");
        exit;
    }
    
    $employee_name = trim($_POST['employee_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validate role (only Admin or Employee allowed)
    $allowed_roles = ['Admin', 'Employee'];
    if (!in_array($role, $allowed_roles)) {
        $_SESSION['error'] = "Error: Invalid role selected. Please choose Admin or Employee.";
        header("Location: adminmanage.php");
        exit;
    }
    
    // Check password match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Error: Passwords do not match.";
        header("Location: adminmanage.php");
        exit;
    }
    
    // Check password length
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Error: Password must be at least 6 characters long.";
        header("Location: adminmanage.php");
        exit;
    }
    
    try {
        // Check if employee_name already exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE employee_name = ?");
        $check_stmt->execute([$employee_name]);
        if ($check_stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Error: Staff member with name '$employee_name' already exists.";
            header("Location: adminmanage.php");
            exit;
        }
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new staff member
        $insert_stmt = $pdo->prepare("INSERT INTO staff (employee_name, password_hash, role) VALUES (?, ?, ?)");
        $insert_stmt->execute([$employee_name, $hashed_password, $role]);
        

        $_SESSION['success'] = "Staff member '$employee_name' added successfully as " . $role . "!";
        
     
        header("Location: adminmanage.php");
        exit;
        
    } catch (PDOException $e) {
       
        error_log("Error creating staff: " . $e->getMessage());
        
    
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = "Error: Duplicate entry. Staff member '$employee_name' already exists.";
        } else {
            $_SESSION['error'] = "Error creating staff: " . $e->getMessage();
        }
        header("Location: adminmanage.php");
        exit;
    }
} else {
   
    header("Location: adminmanage.php");
    exit;
}
?>