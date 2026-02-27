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
    $_SESSION['error'] = "No staff ID provided for deletion.";
    header("Location: adminmanage.php");
    exit;
}

$employee_number = $_GET['id'];

try {
    // First, check if the staff member exists
    $check_stmt = $pdo->prepare("SELECT employee_name, role FROM staff WHERE employee_number = ?");
    $check_stmt->execute([$employee_number]);
    $staff = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$staff) {
        $_SESSION['error'] = "Staff member not found.";
        header("Location: adminmanage.php");
        exit;
    }
    
    // Prevent admin from deleting themselves
    if ($employee_number == $_SESSION['employee_number']) {
        $_SESSION['error'] = "You cannot delete your own account!";
        header("Location: adminmanage.php");
        exit;
    }
    
    // Delete the staff member
    $delete_stmt = $pdo->prepare("DELETE FROM staff WHERE employee_number = ?");
    $delete_stmt->execute([$employee_number]);
    
    // Check if deletion was successful
    if ($delete_stmt->rowCount() > 0) {
        $_SESSION['success'] = "Staff member '" . $staff['employee_name'] . "' (Role: " . $staff['role'] . ") has been deleted successfully.";
    } else {
        $_SESSION['error'] = "No staff member was deleted.";
    }
    
} catch (PDOException $e) {
    // Check for foreign key constraints if you have related tables
    if ($e->errorInfo[1] == 1451) { // MySQL foreign key constraint error
        $_SESSION['error'] = "Cannot delete this staff member because they have related records in other tables.";
    } else {
        $_SESSION['error'] = "Error deleting staff: " . $e->getMessage();
    }
}

// Redirect back to adminmanage.php
header("Location: adminmanage.php");
exit;
?>