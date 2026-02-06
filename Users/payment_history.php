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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Customer';

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$payment_mode = $_GET['payment_mode'] ?? '';

// Build query
$query = "SELECT 
            p.*,
            r.checkin_date,
            r.checkout_date,
            r.room_type,
            r.reservation_id
          FROM payments p
          JOIN customerreservation r ON p.reservation_id = r.reservation_id
          WHERE p.user_id = ?
            AND DATE(p.payment_date) BETWEEN ? AND ?";
          
$params = [$user_id, $start_date, $end_date];

if (!empty($status)) {
    $query .= " AND p.payment_status = ?";
    $params[] = $status;
}

if (!empty($payment_mode)) {
    $query .= " AND p.mode_of_payment = ?";
    $params[] = $payment_mode;
}

$query .= " ORDER BY p.payment_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get summary
$summary_query = "SELECT 
                    COUNT(*) as total_count,
                    SUM(amount_paid) as total_amount,
                    MIN(payment_date) as first_payment,
                    MAX(payment_date) as last_payment
                  FROM payments 
                  WHERE user_id = ?
                    AND DATE(payment_date) BETWEEN ? AND ?";
$summary_stmt = $pdo->prepare($summary_query);
$summary_stmt->execute([$user_id, $start_date, $end_date]);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Resort Reservation</title>
</head>
<body>
    <h1>Payment History</h1>
    <p>Welcome, <?php echo htmlspecialchars($customer_name); ?></p>
    
    <!-- Filter Form -->
    <div style="border: 1px solid #ddd; padding: 15px; margin: 20px 0;">
        <h3>Filter Payments</h3>
        <form method="GET">
            <div>
                <label>Start Date:</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                
                <label>End Date:</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            
            <div style="margin: 10px 0;">
                <label>Payment Status:</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Failed" <?php echo $status == 'Failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
                
                <label>Payment Mode:</label>
                <select name="payment_mode">
                    <option value="">All Modes</option>
                    <option value="GCash" <?php echo $payment_mode == 'GCash' ? 'selected' : ''; ?>>GCash</option>
                    <option value="Bank Transfer" <?php echo $payment_mode == 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="Cash" <?php echo $payment_mode == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="Credit Card" <?php echo $payment_mode == 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
                </select>
            </div>
            
            <button type="submit" style="background: #007bff; color: white; padding: 8px 16px; border: none; cursor: pointer;">
                Filter
            </button>
            <a href="payment_history.php" style="margin-left: 10px;">Clear Filters</a>
        </form>
    </div>
    
    <!-- Summary -->
    <div style="background: #f8f9fa; padding: 15px; margin: 20px 0;">
        <h3>Summary</h3>
        <p>
            <strong>Total Payments:</strong> <?php echo $summary['total_count']; ?> |
            <strong>Total Amount:</strong> ₱<?php echo number_format($summary['total_amount'], 2); ?> |
            <strong>Period:</strong> <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?>
        </p>
    </div>
    
    <!-- Payments Table -->
    <div>
        <h2>All Payments</h2>
        
        <?php if (empty($payments)): ?>
            <p>No payments found for the selected period.</p>
        <?php else: ?>
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Date</th>
                        <th>Reservation</th>
                        <th>Payment Bundle</th>
                        <th>Mode</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td align="center"><?php echo $payment['payment_id']; ?></td>
                        <td align="center"><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                        <td>
                            <?php echo htmlspecialchars($payment['room_type']); ?><br>
                            <small>Reservation #<?php echo $payment['reservation_id']; ?></small>
                        </td>
                        <td><?php echo $payment['payment_bundle']; ?></td>
                        <td><?php echo $payment['mode_of_payment']; ?></td>
                        <td align="right">₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                        <td align="center">
                            <?php
                            $status_color = [
                                'Completed' => 'green',
                                'Pending' => 'orange',
                                'Failed' => 'red'
                            ][$payment['payment_status']] ?? 'black';
                            ?>
                            <span style="color: <?php echo $status_color; ?>;">
                                <?php echo $payment['payment_status']; ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo $payment['transaction_reference'] ?: 'N/A'; ?></small>
                        </td>
                        <td align="center">
                            <a href="payment_details.php?payment_id=<?php echo $payment['payment_id']; ?>">View</a>
                            <?php if ($payment['payment_status'] == 'Pending'): ?>
                                | <a href="pay_now.php?payment_id=<?php echo $payment['payment_id']; ?>">Pay</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination would go here if needed -->
            <div style="margin: 20px 0; text-align: center;">
                <p>Showing <?php echo count($payments); ?> payment(s)</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Export Options -->
    <div style="margin: 30px 0;">
        <h3>Export Options</h3>
        <a href="export_payments.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&status=<?php echo $status; ?>" 
           style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; display: inline-block;">
            Export to CSV
        </a>
        <a href="print_payments.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&status=<?php echo $status; ?>" 
           style="background: #17a2b8; color: white; padding: 8px 16px; text-decoration: none; display: inline-block; margin-left: 10px;">
            Print
        </a>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="payment_dashboard.php">← Back to Payment Dashboard</a> |
        <a href="make_payment.php">Make New Payment</a>
    </div>
</body>
</html>