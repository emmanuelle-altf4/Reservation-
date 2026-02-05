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
$payment_id = $_GET['payment_id'] ?? 0;

if (!$payment_id) {
    die("Invalid payment ID.");
}

// Get payment details
$payment_query = "SELECT 
                    p.*,
                    u.customer_name,
                    u.customer_email,
                    r.checkin_date,
                    r.checkout_date,
                    r.room_type,
                    r.reservation_id,
                    r.status as reservation_status
                  FROM payments p
                  JOIN user u ON p.user_id = u.user_id
                  JOIN customerreservation r ON p.reservation_id = r.reservation_id
                  WHERE p.payment_id = ? AND p.user_id = ?";
$payment_stmt = $pdo->prepare($payment_query);
$payment_stmt->execute([$payment_id, $user_id]);
$payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Payment not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - Resort Reservation</title>
</head>
<body>
    <h1>Payment Details</h1>
    <p><a href="payment_dashboard.php">← Back to Dashboard</a></p>
    
    <!-- Payment Information -->
    <div style="border: 1px solid #ddd; padding: 20px; margin: 20px 0;">
        <h2>Payment #<?php echo $payment['payment_id']; ?></h2>
        
        <table cellpadding="10">
            <tr>
                <td><strong>Payment ID:</strong></td>
                <td><?php echo $payment['payment_id']; ?></td>
            </tr>
            <tr>
                <td><strong>Reservation ID:</strong></td>
                <td><?php echo $payment['reservation_id']; ?></td>
            </tr>
            <tr>
                <td><strong>Customer Name:</strong></td>
                <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Customer Email:</strong></td>
                <td><?php echo htmlspecialchars($payment['customer_email']); ?></td>
            </tr>
            <tr>
                <td><strong>Room Type:</strong></td>
                <td><?php echo htmlspecialchars($payment['room_type']); ?></td>
            </tr>
            <tr>
                <td><strong>Check-in Date:</strong></td>
                <td><?php echo date('M d, Y', strtotime($payment['checkin_date'])); ?></td>
            </tr>
            <tr>
                <td><strong>Check-out Date:</strong></td>
                <td><?php echo date('M d, Y', strtotime($payment['checkout_date'])); ?></td>
            </tr>
            <tr>
                <td><strong>Payment Bundle:</strong></td>
                <td><?php echo $payment['payment_bundle']; ?></td>
            </tr>
            <tr>
                <td><strong>Mode of Payment:</strong></td>
                <td><?php echo $payment['mode_of_payment']; ?></td>
            </tr>
            <tr>
                <td><strong>Total Amount:</strong></td>
                <td>₱<?php echo number_format($payment['total_amount'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Amount Paid:</strong></td>
                <td style="font-weight: bold;">₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Previous Balance:</strong></td>
                <td>₱<?php echo number_format($payment['previous_balance'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>New Balance:</strong></td>
                <td>₱<?php echo number_format($payment['new_balance'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Payment Date:</strong></td>
                <td><?php echo date('M d, Y h:i A', strtotime($payment['payment_date'])); ?></td>
            </tr>
            <tr>
                <td><strong>Due Date:</strong></td>
                <td><?php echo date('M d, Y', strtotime($payment['due_date'])); ?></td>
            </tr>
            <tr>
                <td><strong>Payment Status:</strong></td>
                <td>
                    <?php
                    $status_color = [
                        'Completed' => 'green',
                        'Pending' => 'orange',
                        'Failed' => 'red',
                        'Cancelled' => 'gray',
                        'Refunded' => 'blue'
                    ][$payment['payment_status']] ?? 'black';
                    ?>
                    <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                        <?php echo $payment['payment_status']; ?>
                    </span>
                </td>
            </tr>
            <?php if ($payment['transaction_reference']): ?>
            <tr>
                <td><strong>Transaction Reference:</strong></td>
                <td><?php echo $payment['transaction_reference']; ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($payment['bank_name']): ?>
            <tr>
                <td><strong>Bank Name:</strong></td>
                <td><?php echo $payment['bank_name']; ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($payment['account_number']): ?>
            <tr>
                <td><strong>Account Number:</strong></td>
                <td><?php echo $payment['account_number']; ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($payment['payment_notes']): ?>
            <tr>
                <td><strong>Notes:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($payment['payment_notes'])); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td><strong>Created At:</strong></td>
                <td><?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?></td>
            </tr>
            <tr>
                <td><strong>Last Updated:</strong></td>
                <td><?php echo date('M d, Y h:i A', strtotime($payment['updated_at'])); ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Actions -->
    <div style="margin: 20px 0;">
        <?php if ($payment['payment_status'] == 'Pending'): ?>
            <a href="pay_now.php?payment_id=<?php echo $payment['payment_id']; ?>" 
               style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; display: inline-block;">
                Pay Now
            </a>
        <?php endif; ?>
        
        <?php if ($payment['payment_status'] == 'Completed'): ?>
            <a href="generate_receipt.php?payment_id=<?php echo $payment['payment_id']; ?>" 
               style="background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; margin-left: 10px;">
                Download Receipt
            </a>
        <?php endif; ?>
        
        <a href="payment_history.php" 
           style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; margin-left: 10px;">
            View All Payments
        </a>
    </div>
</body>
</html>