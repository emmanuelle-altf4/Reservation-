<?php
session_start();
if (!isset($_SESSION['employee_name'])) {
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

// $get pilter
$otp_code = $_GET['otp_code'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';


$query = "SELECT * FROM customerreservation WHERE 1=1";
$params = [];

// sexample if gumagana otp filter
if (!empty($otp_code)) {
    $query .= " AND otp_code LIKE ?";
    $params[] = "%$otp_code%";
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}


if (!empty($search)) {
    $query .= " AND customer_name LIKE ?";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);


$user_stmt = $pdo->query("SELECT customer_name, customer_email FROM user");
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
$user_emails = [];
foreach ($users as $user) {
    $user_emails[$user['customer_name']] = $user['customer_email'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations</title>
    <style>
      /* Modern Resort Management System - Manage Reservations */
:root {
    --primary: #0B4F6C;        /* Deep ocean blue */
    --primary-light: #1E6F8F;
    --primary-dark: #083C52;
    --secondary: #DDB771;       /* Sandy gold */
    --secondary-light: #E9CE9C;
    --accent: #F2856D;          /* Coral */
    --accent-light: #F5A692;
    --success: #28a745;
    --success-light: #d4edda;
    --warning: #ffc107;
    --warning-light: #fff3cd;
    --danger: #dc3545;
    --danger-light: #f8d7da;
    --info: #17a2b8;
    --info-light: #d1ecf1;
    --dark: #2C3E50;
    --gray-100: #f8f9fc;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-400: #ced4da;
    --gray-500: #adb5bd;
    --gray-600: #6c757d;
    --gray-700: #495057;
    --gray-800: #343a40;
    --gray-900: #212529;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
    --shadow-colored: 0 4px 12px rgba(11, 79, 108, 0.15);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.05"><path d="M20 20 L40 20 L30 35 Z" fill="%230B4F6C"/><circle cx="70" cy="30" r="5" fill="%23DDB771"/><circle cx="80" cy="40" r="8" fill="%23DDB771"/></svg>');
    min-height: 100vh;
    color: var(--gray-800);
    line-height: 1.6;
}

/* Navbar Styling - Premium Glass Effect */
.navbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.3);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow-md);
}

.navbar .logo {
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    letter-spacing: -0.5px;
    position: relative;
}

.navbar .logo::before {
    content: '🌊';
    font-size: 1.8rem;
    -webkit-text-fill-color: initial;
}

.navbar ul {
    list-style: none;
    display: flex;
    gap: 1rem;
    margin: 0;
    padding: 0;
}

.navbar ul li a {
    text-decoration: none;
    color: var(--gray-600);
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    position: relative;
    overflow: hidden;
}

.navbar ul li a::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(11, 79, 108, 0.1);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.navbar ul li a:hover::before {
    width: 300px;
    height: 300px;
}

.navbar ul li a:hover {
    color: var(--primary);
}

/* Main Container */
.form {
    max-width: 1600px;
    margin: 2rem auto;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 30px;
    padding: 2.5rem;
    box-shadow: var(--shadow-xl);
    border: 1px solid rgba(255,255,255,0.5);
}

/* Header Section */
.form h1 {
    margin: 0 0 0.5rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 2.5rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.form h1::after {
    content: '🏨';
    font-size: 2rem;
    -webkit-text-fill-color: initial;
}

.form p {
    color: var(--gray-600);
    margin-bottom: 2rem;
    font-size: 1.1rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px dashed var(--gray-300);
    position: relative;
}

.form p::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100px;
    height: 2px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
}

/* Filter Section - Modern Card Design */
.filter-section {
    background: linear-gradient(135deg, rgba(11, 79, 108, 0.03) 0%, rgba(221, 183, 113, 0.03) 100%);
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
}

.filter-section::before {
    content: '🔍';
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 3rem;
    opacity: 0.1;
    transform: rotate(10deg);
}

.filter-section h3 {
    margin-bottom: 1.5rem;
    color: var(--primary);
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-section h3 i {
    color: var(--secondary);
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.filter-group {
    animation: slideIn 0.5s ease-out forwards;
}

.filter-group:nth-child(1) { animation-delay: 0.1s; }
.filter-group:nth-child(2) { animation-delay: 0.2s; }
.filter-group:nth-child(3) { animation-delay: 0.3s; }

.filter-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--gray-700);
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(11, 79, 108, 0.1);
}

.filter-group input:hover,
.filter-group select:hover {
    border-color: var(--secondary);
}

.filter-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 1rem;
}

/* Button Styles */
.btn {
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:hover::before {
    width: 300px;
    height: 300px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    box-shadow: var(--shadow-colored);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(11, 79, 108, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(108, 117, 125, 0.4);
}

/* Stats Summary Cards */
.stats-summary {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.stat-item {
    padding: 0.8rem 1.5rem;
    background: white;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--primary);
    animation: fadeIn 0.5s ease-out forwards;
}

.stat-item:nth-child(1) { border-left-color: var(--primary); animation-delay: 0.1s; }
.stat-item:nth-child(2) { border-left-color: var(--warning); animation-delay: 0.2s; }
.stat-item:nth-child(3) { border-left-color: var(--success); animation-delay: 0.3s; }
.stat-item:nth-child(4) { border-left-color: var(--danger); animation-delay: 0.4s; }

/* Table Design - Modern Card Style */
.reservation-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    margin-top: 1rem;
}

.reservation-table thead tr {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 15px;
    overflow: hidden;
}

.reservation-table th {
    color: white;
    font-weight: 600;
    text-align: left;
    padding: 1.2rem 1rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.reservation-table th:first-child {
    border-top-left-radius: 15px;
    border-bottom-left-radius: 15px;
}

.reservation-table th:last-child {
    border-top-right-radius: 15px;
    border-bottom-right-radius: 15px;
}

.reservation-table td {
    padding: 1.2rem 1rem;
    background: white;
    color: var(--gray-700);
    font-size: 0.9rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    border-bottom: 2px solid transparent;
}

/* Row hover effect - AFFECT EVENT */
.reservation-table tbody tr {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: slideInRow 0.5s ease-out forwards;
    opacity: 0;
    transform: translateX(-20px);
}

@keyframes slideInRow {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Delayed animation for each row */
.reservation-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
.reservation-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
.reservation-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
.reservation-table tbody tr:nth-child(4) { animation-delay: 0.4s; }
.reservation-table tbody tr:nth-child(5) { animation-delay: 0.5s; }
.reservation-table tbody tr:nth-child(6) { animation-delay: 0.6s; }
.reservation-table tbody tr:nth-child(7) { animation-delay: 0.7s; }
.reservation-table tbody tr:nth-child(8) { animation-delay: 0.8s; }
.reservation-table tbody tr:nth-child(9) { animation-delay: 0.9s; }
.reservation-table tbody tr:nth-child(10) { animation-delay: 1s; }

/* AFFECT EVENT - Row hover effect */
.reservation-table tbody tr:hover {
    transform: translateY(-3px) scale(1.01);
    box-shadow: var(--shadow-lg);
    background: white;
    position: relative;
    z-index: 10;
}

.reservation-table tbody tr:hover td {
    background: linear-gradient(90deg, white, var(--gray-100));
    border-bottom: 2px solid var(--secondary);
}

/* Add glow effect on hover */
.reservation-table tbody tr:hover::after {
    content: '';
    position: absolute;
    top: -5px;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    border-radius: 5px 5px 0 0;
}

.reservation-table td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.reservation-table td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

/* Customer Info */
.customer-email {
    font-size: 0.8rem;
    color: var(--gray-500);
    display: block;
    margin-top: 0.25rem;
}

/* Status Badges */
.status-badge {
    padding: 0.4rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
    text-align: center;
    min-width: 90px;
    position: relative;
    overflow: hidden;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.status-badge:hover::before {
    left: 100%;
}

.status-confirmed {
    background: linear-gradient(135deg, var(--success-light) 0%, #c3e6cb 100%);
    color: var(--success);
    border-left: 3px solid var(--success);
}

.status-pending {
    background: linear-gradient(135deg, var(--warning-light) 0%, #ffe69c 100%);
    color: #856404;
    border-left: 3px solid var(--warning);
}

.status-cancelled {
    background: linear-gradient(135deg, var(--danger-light) 0%, #f5c6cb 100%);
    color: var(--danger);
    border-left: 3px solid var(--danger);
}

/* OTP Code Styling */
.otp-code {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    letter-spacing: 2px;
    color: var(--primary);
    background: linear-gradient(135deg, rgba(11, 79, 108, 0.05) 0%, rgba(221, 183, 113, 0.05) 100%);
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    cursor: pointer;
    display: inline-block;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.otp-code:hover {
    background: linear-gradient(135deg, rgba(11, 79, 108, 0.1) 0%, rgba(221, 183, 113, 0.1) 100%);
    border-color: var(--secondary);
    transform: scale(1.05);
    box-shadow: var(--shadow-sm);
}

/* Actions Container */
.actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-link {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
    background: white;
    border: 1px solid var(--gray-200);
}

.action-link i {
    font-size: 0.9rem;
}

.action-link:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Edit button */
.action-link:first-child {
    color: var(--primary);
    border-left: 3px solid var(--primary);
}
.action-link:first-child:hover {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border-color: var(--primary);
}

/* Delete button */
.action-link:nth-child(2) {
    color: var(--danger);
    border-left: 3px solid var(--danger);
}
.action-link:nth-child(2):hover {
    background: linear-gradient(135deg, var(--danger) 0%, #ff6b6b 100%);
    color: white;
    border-color: var(--danger);
}

/* Verify button */
.action-link:nth-child(3) {
    color: var(--success);
    border-left: 3px solid var(--success);
}
.action-link:nth-child(3):hover {
    background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
    color: white;
    border-color: var(--success);
}

/* Regenerate OTP button */
.action-link:nth-child(4) {
    color: var(--info);
    border-left: 3px solid var(--info);
}
.action-link:nth-child(4):hover {
    background: linear-gradient(135deg, var(--info) 0%, #1fc8e3 100%);
    color: white;
    border-color: var(--info);
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Empty state styling */
td[colspan="11"] {
    text-align: center;
    padding: 3rem !important;
    background: linear-gradient(135deg, var(--gray-100) 0%, white 100%);
    border-radius: 20px !important;
    font-size: 1.1rem;
    color: var(--gray-600);
    animation: pulse 2s infinite;
}

td[colspan="11"] i {
    font-size: 3rem;
    color: var(--secondary);
    margin-bottom: 1rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .form {
        margin: 1rem;
        padding: 1.5rem;
    }
    
    .reservation-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .actions {
        flex-direction: column;
    }
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }
    
    .navbar ul {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .form h1 {
        font-size: 2rem;
        flex-direction: column;
        text-align: center;
    }
    
    .stats-summary {
        justify-content: center;
    }
    
    .reservation-table td:first-child,
    .reservation-table td:last-child {
        border-radius: 0;
    }
    
    /* Mobile card view */
    .reservation-table,
    .reservation-table thead,
    .reservation-table tbody,
    .reservation-table th,
    .reservation-table td,
    .reservation-table tr {
        display: block;
    }
    
    .reservation-table thead {
        display: none;
    }
    
    .reservation-table tr {
        margin-bottom: 1.5rem;
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid var(--gray-200);
        background: white;
    }
    
    .reservation-table td {
        padding: 1rem;
        text-align: right;
        position: relative;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .reservation-table td:last-child {
        border-bottom: none;
    }
    
    .reservation-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: var(--primary);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    .actions {
        flex-direction: row;
        justify-content: flex-end;
    }
}

/* Print Styles */
@media print {
    .navbar, .filter-section, .actions, .btn {
        display: none !important;
    }
    
    .form {
        box-shadow: none;
        border: 1px solid #ddd;
        padding: 1rem;
    }
    
    .reservation-table td {
        border: 1px solid #ddd;
    }
}
    </style>
</head>
<body>
<div class="navbar">
    <a href="dashboard.php" class="logo">Resort Staff Panel</a>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="manage_reservations.php">Manage Reservations</a></li>
        <li><a href="staff_payments.php">Payments</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form">
    <h1>Manage Reservations</h1>
    <p>All customer reservations are listed below. Staff can edit or delete entries.</p>
    
    <!-- exaple lang to HAHAHAHHA  -->
    <div class="filter-section">
        <h3>Filter Reservations</h3>
        <form method="GET">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Search by Customer Name</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Enter customer name">
                </div>
                
                <div class="filter-group">
                    <label>Filter by OTP Code</label>
                    <input type="text" name="otp_code" value="<?php echo htmlspecialchars($otp_code); ?>" 
                           placeholder="Enter OTP code (6 digits)" maxlength="6" pattern="\d{0,6}">
                </div>
                
                <div class="filter-group">
                    <label>Filter by Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Confirmed" <?php echo $status == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="Cancelled" <?php echo $status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="manage_reservations.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>
    

    <?php
    $total_reservations = count($reservations);
    $pending_count = 0;
    $confirmed_count = 0;
    $cancelled_count = 0;
    
    foreach ($reservations as $res) {
        if ($res['status'] == 'Pending') $pending_count++;
        if ($res['status'] == 'Confirmed') $confirmed_count++;
        if ($res['status'] == 'Cancelled') $cancelled_count++;
    }
    ?>
    
    <div class="stats-summary">
        <div class="stat-item">Total: <?php echo $total_reservations; ?></div>
        <div class="stat-item">Pending: <?php echo $pending_count; ?></div>
        <div class="stat-item">Confirmed: <?php echo $confirmed_count; ?></div>
        <div class="stat-item">Cancelled: <?php echo $cancelled_count; ?></div>
    </div>

    <table class="reservation-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Room Type</th>
                <th>Guests</th>
                <th>Status</th>
                <th>OTP Code</th>
                <th>OTP Expiry</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reservations)): ?>
                <tr>
                    <td colspan="11" style="text-align: center; padding: 2rem; color: #6c757d;">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        No reservations found matching your criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($reservations as $res): ?>
                <tr>
                    <td><?php echo $res['reservation_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($res['customer_name']); ?></strong><br>
                        <span class="customer-email">
                            <?php 
                       
                            echo isset($user_emails[$res['customer_name']]) 
                                ? htmlspecialchars($user_emails[$res['customer_name']]) 
                                : 'Email not found';
                            ?>
                        </span>
                    </td>
                    <td><?php echo $res['checkin_date']; ?></td>
                    <td><?php echo $res['checkout_date']; ?></td>
                    <td><?php echo $res['room_type']; ?></td>
                    <td><?php echo $res['guests']; ?></td>
                    <td>
                        <?php
                        $status_class = [
                            'Confirmed' => 'status-confirmed',
                            'Pending' => 'status-pending',
                            'Cancelled' => 'status-cancelled'
                        ][$res['status']] ?? '';
                        ?>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo $res['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($res['otp_code']): ?>
                            <span class="otp-code" title="Click to copy" data-otp="<?php echo $res['otp_code']; ?>">
                                <?php echo $res['otp_code']; ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #6c757d;">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if ($res['otp_expiry']) {
                            $expiry = new DateTime($res['otp_expiry']);
                            $now = new DateTime();
                            if ($expiry > $now) {
                                echo '<span style="color: #28a745;">' . 
                                     date('M d, Y h:i A', strtotime($res['otp_expiry'])) . 
                                     ' (Active)</span>';
                            } else {
                                echo '<span style="color: #dc3545;">' . 
                                     date('M d, Y h:i A', strtotime($res['otp_expiry'])) . 
                                     ' (Expired)</span>';
                            }
                        } else {
                            echo '<span style="color: #6c757d;">N/A</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($res['created_at'])); ?></td>
                    <td>
                        <div class="actions">
                            <a href="update_reservation.php?id=<?php echo $res['reservation_id']; ?>" 
                               class="action-link" title="Edit reservation">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            <a href="delete_reservation.php?id=<?php echo $res['reservation_id']; ?>" 
                               onclick="return confirm('Are you sure you want to delete this reservation?')" 
                               class="action-link" title="Delete reservation" style="color: #dc3545;">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            
                       
                            <?php if ($res['status'] == 'Pending' && $res['otp_code']): ?>
                                <a href="verify_otp.php?id=<?php echo $res['reservation_id']; ?>" 
                                   class="action-link" title="Verify OTP" style="color: #28a745;">
                                    <i class="fas fa-check-circle"></i> Verify
                                </a>
                            <?php endif; ?>
                            
                           
                            <?php 
                            if ($res['otp_expiry']) {
                                $expiry = new DateTime($res['otp_expiry']);
                                $now = new DateTime();
                                if ($expiry <= $now && $res['status'] == 'Pending'):
                            ?>
                                <a href="regenerate_otp.php?id=<?php echo $res['reservation_id']; ?>" 
                                   onclick="return confirm('Regenerate OTP for this reservation?')" 
                                   class="action-link" title="Regenerate OTP" style="color: #17a2b8;">
                                    <i class="fas fa-redo"></i> Regen OTP
                                </a>
                            <?php 
                                endif;
                            }
                            ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {

    const otpElements = document.querySelectorAll('.otp-code');
    otpElements.forEach(element => {
        element.addEventListener('click', function() {
            const otp = this.getAttribute('data-otp') || this.textContent;
            if (otp && otp !== 'N/A') {
                navigator.clipboard.writeText(otp).then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    this.style.color = '#28a745';
                    this.style.backgroundColor = '#d4edda';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.color = '#007BFF';
                        this.style.backgroundColor = '';
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy OTP: ', err);
                    alert('Failed to copy OTP to clipboard');
                });
            }
        });
    });


    const filterForm = document.querySelector('form');
    const otpInput = filterForm.querySelector('input[name="otp_code"]');
    
    otpInput.addEventListener('input', function() {
   
        this.value = this.value.replace(/\D/g, '');
   
        if (this.value.length > 6) {
            this.value = this.value.slice(0, 6);
        }
    });

    filterForm.addEventListener('submit', function(e) {
        if (otpInput.value && otpInput.value.length !== 6) {
            alert('OTP code must be exactly 6 digits');
            otpInput.focus();
            e.preventDefault();
            return false;
        }
    });

  
    const tableRows = document.querySelectorAll('.reservation-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.002)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            this.style.transition = 'all 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>
</body>
</html>

if <row class="affect"> event</row>
