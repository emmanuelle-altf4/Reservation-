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
        /* My CSS is AI */
        .navbar {
            background-color: #fff;
            border-bottom: 1px solid #ddd;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .navbar .logo {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }

        .navbar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .navbar ul li a:hover {
            color: #007BFF;
        }

        .form {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .form h1 {
            margin-bottom: 1rem;
            color: #333;
        }

        .form p {
            margin-bottom: 1rem;
            color: #555;
        }

        /* Filter Section */
        .filter-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #dee2e6;
        }

        .filter-section h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.1rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-size: 0.9rem;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .filter-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #007BFF;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Table Styling */
        .reservation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }

        .reservation-table th {
            background: #f5f5f5;
            color: #333;
            text-align: left;
            padding: 0.75rem 1rem;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        .reservation-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #eee;
            color: #555;
        }

        .reservation-table tr:nth-child(even) {
            background: #fafafa;
        }

        .reservation-table tr:hover {
            background: #f0f8ff;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        /* OTP Column */
        .otp-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 1px;
            color: #007BFF;
            cursor: pointer;
        }

        .otp-code:hover {
            background: #f0f8ff;
        }

        /* Actions */
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-link {
            color: #007BFF;
            text-decoration: none;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        /* Summary Stats */
        .stats-summary {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .stat-item {
            padding: 0.5rem 1rem;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        /* Customer Info */
        .customer-email {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .reservation-table {
                display: block;
                overflow-x: auto;
            }
            
            .actions {
                flex-direction: column;
                gap: 0.25rem;
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
