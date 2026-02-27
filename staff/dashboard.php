<?php
session_start();
// Protect page: only staff accounts should access
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

// Fetch all reservations
$stmt = $pdo->query("SELECT * FROM customerreservation ORDER BY created_at DESC");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
   <style>
   /* Navbar styling */
/* Modern Resort Management System CSS */
:root {
    --primary: #0B4F6C;      /* Deep ocean blue */
    --primary-light: #1E6F8F;
    --secondary: #DDB771;     /* Sandy gold */
    --accent: #F2856D;        /* Coral */
    --dark: #145369;
    --light: #F8F9FA;
    --success: #28a745;
    --warning: #ffc107;
    --danger: #dc3545;
    --gray-100: #f8f9fc;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-600: #6c757d;
    --gray-800: #343a40;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    color: var(--gray-800);
    line-height: 1.6;
}

/* Navbar Styling - Modern Glass Effect */
.navbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.3);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'Inter', sans-serif;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow-md);
}

.navbar .logo {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    letter-spacing: -0.5px;
}

.navbar .logo::before {
    content: '🌴';
    font-size: 1.8rem;
}

.navbar ul {
    list-style: none;
    display: flex;
    gap: 2rem;
    margin: 0;
    padding: 0;
}

.navbar ul li a {
    text-decoration: none;
    color: var(--gray-600);
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    position: relative;
}

.navbar ul li a:hover {
    color: var(--primary);
    background: rgba(11, 79, 108, 0.05);
}

.navbar ul li a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transition: width 0.3s ease;
}

.navbar ul li a:hover::after {
    width: 80%;
}

/* Dashboard Container */
.form {
    max-width: 1400px;
    margin: 2rem auto;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-xl);
    border: 1px solid rgba(255,255,255,0.5);
}

.form h1 {
    margin: 0 0 0.5rem;
    color: var(--primary);
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.form h1::before {
    content: '👋';
    font-size: 2rem;
    animation: wave 2s infinite;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    75% { transform: rotate(-10deg); }
}

.form p {
    color: var(--gray-600);
    margin-bottom: 2rem;
    font-size: 1.1rem;
    padding-bottom: 1rem;
    border-bottom: 2px dashed var(--gray-300);
}

/* Modern Table Design */
.reservation-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
    margin-top: 1rem;
}

.reservation-table thead tr {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 12px;
    overflow: hidden;
}

.reservation-table th {
    color: white;
    font-weight: 600;
    text-align: left;
    padding: 1rem 1.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.reservation-table th:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.reservation-table th:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

.reservation-table td {
    padding: 1.2rem 1.5rem;
    background: white;
    color: var(--gray-800);
    font-size: 0.95rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.reservation-table tr {
    border-radius: 12px;
    transition: all 0.3s ease;
}

.reservation-table tr:hover td {
    background: var(--gray-100);
    transform: scale(1.01);
    box-shadow: var(--shadow-md);
}

.reservation-table tr:hover td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.reservation-table tr:hover td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

/* Status Badge Styling */
.reservation-table td:nth-child(7) {
    font-weight: 600;
}

.reservation-table td:nth-child(7):contains('Confirmed') {
    color: var(--success);
}

.reservation-table td:nth-child(7):contains('Pending') {
    color: var(--warning);
}

.reservation-table td:nth-child(7):contains('Cancelled') {
    color: var(--danger);
}

/* Action Buttons */
.reservation-table td:last-child {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.reservation-table td a {
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.reservation-table td a:first-child {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(11, 79, 108, 0.3);
}

.reservation-table td a:first-child:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(11, 79, 108, 0.4);
}

.reservation-table td a:last-child {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(238, 82, 83, 0.3);
}

.reservation-table td a:last-child:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(238, 82, 83, 0.4);
}

/* Room Type Badges */
.reservation-table td:nth-child(5) {
    font-weight: 500;
    position: relative;
}

.reservation-table td:nth-child(5):before {
    content: '🛏️';
    margin-right: 0.5rem;
    opacity: 0.7;
}

/* Guest Count Styling */
.reservation-table td:nth-child(6) {
    font-weight: 600;
    color: var(--primary);
}

.reservation-table td:nth-child(6):after {
    content: ' 👥';
    opacity: 0.7;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .form {
        margin: 1rem;
        padding: 1.5rem;
    }
    
    .reservation-table {
        border-spacing: 0 5px;
    }
    
    .reservation-table td, 
    .reservation-table th {
        padding: 1rem;
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
    
    .navbar ul li a {
        padding: 0.5rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .form h1 {
        font-size: 1.8rem;
    }
    
    /* Mobile Table Layout */
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
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--gray-300);
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
        justify-content: flex-end;
    }
    
    .reservation-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: var(--primary);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        margin-right: 1rem;
    }
    
    /* Adjust action buttons for mobile */
    .reservation-table td:last-child {
        gap: 0.5rem;
    }
    
    .reservation-table td a {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.reservation-table tr {
    animation: fadeInUp 0.5s ease-out forwards;
    animation-delay: calc(var(--row-index) * 0.1s);
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: var(--gray-200);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary) 100%);
}

/* Loading Skeleton Animation */
@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

/* Print Styles */
@media print {
    .navbar, 
    .reservation-table td:last-child,
    .reservation-table td:nth-child(8) {
        display: none;
    }
    
    .form {
        box-shadow: none;
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
        <li><a href="manage_reservation.php">Manage Reservations</a></li>
        <li><a href="walkin_reservation.php">Make Reservation (for walk in)</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form">
    <h1>Welcome,KOOKY NAKA AI LANG CSS NETO AH TANG INA AHHAHAHAHAH <?php echo htmlspecialchars($_SESSION['employee_name']); ?>!</h1>
    <p>Here are the latest reservations:</p>

    <table class="reservation-table">
        <thead>
            <tr>
                <th>Reservation ID</th>
                <th>Customer Name</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Room Type</th>
                <th>Guests</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $res): ?>
            <tr>
                <td data-label="Reservation ID"><?php echo $res['reservation_id']; ?></td>
                <td data-label="Customer Name"><?php echo htmlspecialchars($res['customer_name']); ?></td>
                <td data-label="Check-in"><?php echo $res['checkin_date']; ?></td>
                <td data-label="Check-out"><?php echo $res['checkout_date']; ?></td>
                <td data-label="Room Type"><?php echo $res['room_type']; ?></td>
                <td data-label="Guests"><?php echo $res['guests']; ?></td>
                <td data-label="Status"><?php echo $res['status']; ?></td>
                <td>
                    <a href="update_reservation.php?id=<?php echo $res['reservation_id']; ?>">Edit</a> |
                    <a href="delete_reservation.php?id=<?php echo $res['reservation_id']; ?>" onclick="return confirm('Delete this reservation?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
