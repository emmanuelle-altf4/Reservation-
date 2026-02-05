<?php
session_start();

// Database connection
$host = "localhost";
$dbname = "resortreservation";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if staff is logged in
if (!isset($_SESSION['employee_number'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['employee_number'];
$staff_name = $_SESSION['employee_name'] ?? 'Staff';

// Handle actions
$message = '';
$message_type = '';

// Verify payment
if (isset($_GET['verify_payment'])) {
    $payment_id = $_GET['verify_payment'];
    
    try {
        $update_query = "UPDATE payments SET 
                        payment_status = 'Completed',
                        processed_by = ?,
                        updated_at = NOW()
                        WHERE payment_id = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$staff_id, $payment_id]);
        
        $message = "Payment verified successfully!";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error verifying payment: " . $e->getMessage();
        $message_type = "error";
    }
}

// Reject payment
if (isset($_GET['reject_payment'])) {
    $payment_id = $_GET['reject_payment'];
    $reason = $_GET['reason'] ?? 'No reason provided';
    
    try {
        $update_query = "UPDATE payments SET 
                        payment_status = 'Failed',
                        payment_notes = CONCAT(IFNULL(payment_notes, ''), '\nRejected: ', ?),
                        processed_by = ?,
                        updated_at = NOW()
                        WHERE payment_id = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$reason, $staff_id, $payment_id]);
        
        $message = "Payment rejected!";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error rejecting payment: " . $e->getMessage();
        $message_type = "error";
    }
}

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query for payments
$query = "SELECT 
            p.*,
            u.customer_name,
            u.customer_email,
            r.checkin_date,
            r.checkout_date,
            r.room_type,
            r.status as reservation_status,
            s.employee_name as processed_by_name
          FROM payments p
          JOIN user u ON p.user_id = u.user_id
          JOIN customerreservation r ON p.reservation_id = r.reservation_id
          LEFT JOIN staff s ON p.processed_by = s.employee_number
          WHERE DATE(p.created_at) BETWEEN ? AND ?";
          
$params = [$start_date, $end_date];

if (!empty($status)) {
    $query .= " AND p.payment_status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $query .= " AND (u.customer_name LIKE ? 
                OR u.customer_email LIKE ? 
                OR p.transaction_reference LIKE ?
                OR p.payment_id = ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = is_numeric($search) ? $search : -1;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "SELECT 
                  COUNT(*) as total_payments,
                  SUM(CASE WHEN payment_status = 'Completed' THEN amount_paid ELSE 0 END) as total_collected,
                  SUM(CASE WHEN payment_status = 'Pending' THEN amount_paid ELSE 0 END) as pending_amount,
                  SUM(CASE WHEN payment_status = 'Failed' THEN amount_paid ELSE 0 END) as failed_amount,
                  AVG(amount_paid) as average_payment,
                  payment_status,
                  COUNT(*) as status_count
                FROM payments 
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY payment_status";
$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute([$start_date, $end_date]);
$stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_collected = 0;
$total_pending = 0;
$total_failed = 0;

foreach ($stats as $stat) {
    if ($stat['payment_status'] == 'Completed') {
        $total_collected = $stat['total_collected'];
    } elseif ($stat['payment_status'] == 'Pending') {
        $total_pending = $stat['pending_amount'];
    } elseif ($stat['payment_status'] == 'Failed') {
        $total_failed = $stat['failed_amount'];
    }
}

// Get today's payments
$today_query = "SELECT COUNT(*) as today_count, 
                       SUM(amount_paid) as today_total 
                FROM payments 
                WHERE DATE(created_at) = CURDATE() 
                  AND payment_status = 'Completed'";
$today_stmt = $pdo->query($today_query);
$today_stats = $today_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Payment Management</title>
</head>
<body>
    <h1>Staff Payment Management</h1>
    <p>Welcome, <?php echo htmlspecialchars($staff_name); ?> (Employee #<?php echo $staff_id; ?>)</p>
    
    <?php if ($message): ?>
        <div style="color: <?php echo $message_type == 'success' ? 'green' : 'red'; ?>; 
                    padding: 10px; border: 1px solid; margin: 10px 0;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Quick Stats -->
    <div style="display: flex; gap: 20px; margin: 20px 0;">
        <div style="border: 1px solid #ddd; padding: 15px; flex: 1;">
            <h3>Total Collected</h3>
            <h2>₱<?php echo number_format($total_collected, 2); ?></h2>
            <small>Completed payments</small>
        </div>
        <div style="border: 1px solid #ddd; padding: 15px; flex: 1;">
            <h3>Pending Amount</h3>
            <h2 style="color: orange;">₱<?php echo number_format($total_pending, 2); ?></h2>
            <small>Awaiting verification</small>
        </div>
        <div style="border: 1px solid #ddd; padding: 15px; flex: 1;">
            <h3>Today's Collection</h3>
            <h2>₱<?php echo number_format($today_stats['today_total'] ?? 0, 2); ?></h2>
            <small><?php echo $today_stats['today_count'] ?? 0; ?> payments today</small>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div style="border: 1px solid #ddd; padding: 15px; margin: 20px 0;">
        <h3>Filter Payments</h3>
        <form method="GET">
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <div>
                    <label>Start Date:</label><br>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div>
                    <label>End Date:</label><br>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div>
                    <label>Status:</label><br>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Failed" <?php echo $status == 'Failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>
                <div>
                    <label>Search:</label><br>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Name, Email, Reference">
                </div>
            </div>
            <button type="submit" style="background: #007bff; color: white; padding: 8px 16px; border: none; cursor: pointer;">
                Filter
            </button>
            <a href="staff_payments.php" style="margin-left: 10px;">Clear Filters</a>
        </form>
    </div>
    
    <!-- Payments Table -->
    <h2>Payment Transactions (<?php echo count($payments); ?> found)</h2>
    
    <?php if (empty($payments)): ?>
        <p>No payments found for the selected criteria.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Reservation</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th>Processed By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td align="center"><?php echo $payment['payment_id']; ?></td>
                    <td align="center">
                        <?php echo date('M d', strtotime($payment['created_at'])); ?><br>
                        <small><?php echo date('h:i A', strtotime($payment['created_at'])); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($payment['customer_name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($payment['customer_email']); ?></small><br>
                      
                    </td>
                    <td>
                        Reservation #<?php echo $payment['reservation_id']; ?><br>
                        <small><?php echo htmlspecialchars($payment['room_type']); ?></small><br>
                        <small><?php echo date('M d', strtotime($payment['checkin_date'])); ?> - <?php echo date('M d', strtotime($payment['checkout_date'])); ?></small>
                    </td>
                    <td align="right">
                        <strong>₱<?php echo number_format($payment['amount_paid'], 2); ?></strong><br>
                        <small>Total: ₱<?php echo number_format($payment['total_amount'], 2); ?></small><br>
                        <small>Balance: ₱<?php echo number_format($payment['new_balance'], 2); ?></small>
                    </td>
                    <td align="center">
                        <?php echo $payment['mode_of_payment'] ?: 'Not Set'; ?><br>
                        <small><?php echo $payment['payment_bundle']; ?></small>
                    </td>
                    <td align="center">
                        <?php
                        $status_color = [
                            'Completed' => 'green',
                            'Pending' => 'orange',
                            'Failed' => 'red',
                            'Cancelled' => 'gray'
                        ][$payment['payment_status']] ?? 'black';
                        ?>
                        <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                            <?php echo $payment['payment_status']; ?>
                        </span>
                    </td>
                    <td>
                        <small><?php echo $payment['transaction_reference'] ?: 'N/A'; ?></small>
                    </td>
                    <td align="center">
                        <?php echo $payment['processed_by_name'] ?: 'Not Processed'; ?>
                    </td>
                    <td align="center">
                        <!-- View Details -->
                        <a href="staff_payment_details.php?id=<?php echo $payment['payment_id']; ?>" 
                           style="display: block; margin: 2px 0;">View</a>
                        
                        <!-- Actions based on status -->
                        <?php if ($payment['payment_status'] == 'Pending'): ?>
                            <a href="staff_payments.php?verify_payment=<?php echo $payment['payment_id']; ?>" 
                               onclick="return confirm('Verify this payment?')"
                               style="color: green; display: block; margin: 2px 0;">Verify</a>
                            <a href="#" 
                               onclick="showRejectPrompt(<?php echo $payment['payment_id']; ?>)"
                               style="color: red; display: block; margin: 2px 0;">Reject</a>
                        <?php elseif ($payment['payment_status'] == 'Completed'): ?>
                            <a href="generate_receipt.php?id=<?php echo $payment['payment_id']; ?>" 
                               style="color: blue; display: block; margin: 2px 0;">Receipt</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <div style="margin: 30px 0; padding: 15px; border: 1px solid #ddd;">
        <h3>Quick Actions</h3>
        <div style="display: flex; gap: 10px;">
            <a href="staff_add_payment.php" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none;">
                + Add Manual Payment
            </a>
            <a href="staff_pending_payments.php" style="background: #ffc107; color: black; padding: 10px 15px; text-decoration: none;">
                ⏳ View Pending Payments
            </a>
            <a href="export_payments.php?<?php echo http_build_query($_GET); ?>" 
               style="background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none;">
                📊 Export to Excel
            </a>
            <a href="staff_dashboard.php" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none;">
                ← Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Payment Statistics -->
    <div style="border: 1px solid #ddd; padding: 15px; margin: 20px 0;">
        <h3>Payment Statistics (<?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?>)</h3>
        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <tr>
                <th>Status</th>
                <th>Count</th>
                <th>Total Amount</th>
                <th>Average Payment</th>
            </tr>
            <?php foreach ($stats as $stat): ?>
            <tr>
                <td align="center">
                    <?php 
                    $status_color = [
                        'Completed' => 'green',
                        'Pending' => 'orange',
                        'Failed' => 'red'
                    ][$stat['payment_status']] ?? 'black';
                    ?>
                    <span style="color: <?php echo $status_color; ?>;">
                        <?php echo $stat['payment_status']; ?>
                    </span>
                </td>
                <td align="center"><?php echo $stat['status_count']; ?></td>
                <td align="right">₱<?php echo number_format($stat['total_collected'] ?? $stat['pending_amount'] ?? $stat['failed_amount'] ?? 0, 2); ?></td>
                <td align="right">₱<?php echo number_format($stat['average_payment'] ?? 0, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <script>
    function showRejectPrompt(paymentId) {
        var reason = prompt("Enter reason for rejection:", "Payment verification failed");
        if (reason !== null) {
            window.location.href = 'staff_payments.php?reject_payment=' + paymentId + '&reason=' + encodeURIComponent(reason);
        }
    }
    </script>
</body>
</html>