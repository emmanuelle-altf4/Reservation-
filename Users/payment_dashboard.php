<?php
session_start();


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


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Customer';


$summary_query = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(amount_paid) as total_paid,
                    MIN(payment_date) as first_payment_date,
                    MAX(payment_date) as last_payment_date,
                    SUM(CASE WHEN payment_status = 'Pending' THEN amount_paid ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN payment_status = 'Completed' THEN amount_paid ELSE 0 END) as completed_amount
                  FROM payments 
                  WHERE user_id = ?";
$summary_stmt = $pdo->prepare($summary_query);
$summary_stmt->execute([$user_id]);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);


if (!$summary) {
    $summary = [
        'total_payments' => 0,
        'total_paid' => 0,
        'first_payment_date' => null,
        'last_payment_date' => null,
        'pending_amount' => 0,
        'completed_amount' => 0
    ];
}


$table_check = $pdo->query("SHOW TABLES LIKE 'payments'");
if ($table_check->rowCount() == 0) {
    echo "<h2>Payment Dashboard</h2>";
    echo "<p>Welcome, " . htmlspecialchars($customer_name) . "!</p>";
    echo "<p style='color: red;'>Payment system is not yet set up. Please contact admin.</p>";
    echo "<p><a href='my_reservations.php'>View My Reservations</a></p>";
    exit();
}


$pending_payments = [];
try {
    $pending_query = "SELECT 
                        p.*,
                        r.checkin_date,
                        r.checkout_date,
                        r.room_type,
                        r.status as reservation_status
                      FROM payments p
                      JOIN customerreservation r ON p.reservation_id = r.reservation_id
                      WHERE p.user_id = ? AND p.payment_status = 'Pending'
                      ORDER BY p.due_date ASC";
    $pending_stmt = $pdo->prepare($pending_query);
    $pending_stmt->execute([$user_id]);
    $pending_payments = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pending_payments = [];
}


$completed_payments = [];
try {
    $completed_query = "SELECT 
                          p.*,
                          r.checkin_date,
                          r.checkout_date,
                          r.room_type
                        FROM payments p
                        JOIN customerreservation r ON p.reservation_id = r.reservation_id
                        WHERE p.user_id = ? AND p.payment_status = 'Completed'
                        ORDER BY p.payment_date DESC
                        LIMIT 10";
    $completed_stmt = $pdo->prepare($completed_query);
    $completed_stmt->execute([$user_id]);
    $completed_payments = $completed_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $completed_payments = [];
}


$upcoming_reservations = [];
try {
    $upcoming_query = "SELECT 
                          r.reservation_id,
                          r.checkin_date,
                          r.checkout_date,
                          r.room_type,
                          r.status as reservation_status
                        FROM customerreservation r
                        WHERE r.user_id = ? 
                          AND r.status IN ('Confirmed', 'Pending')
                          AND r.checkin_date >= CURDATE()
                        ORDER BY r.checkin_date ASC";
    $upcoming_stmt = $pdo->prepare($upcoming_query);
    $upcoming_stmt->execute([$user_id]);
    $upcoming_reservations = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);
    

    foreach ($upcoming_reservations as &$reservation) {
     
        $payment_query = "SELECT 
                            SUM(amount_paid) as paid_amount,
                            COUNT(*) as payment_count
                          FROM payments 
                          WHERE reservation_id = ? AND user_id = ?";
        $payment_stmt = $pdo->prepare($payment_query);
        $payment_stmt->execute([$reservation['reservation_id'], $user_id]);
        $payment_data = $payment_stmt->fetch(PDO::FETCH_ASSOC);
        
        $reservation['paid_amount'] = $payment_data['paid_amount'] ?? 0;
        
        
        switch ($reservation['room_type']) {
            case 'Standard Villa':
                $reservation['total_amount'] = 15000.00;
                break;
            case 'Deluxe Two-Bedroom Villa':
                $reservation['total_amount'] = 25000.00;
                break;
            default:
                $reservation['total_amount'] = 20000.00;
        }
        
        $reservation['balance'] = $reservation['total_amount'] - $reservation['paid_amount'];
        $reservation['payment_status'] = $reservation['balance'] <= 0 ? 'Paid' : 
                                        ($reservation['paid_amount'] > 0 ? 'Partial' : 'Unpaid');
    }
    unset($reservation);
} catch (Exception $e) {
    $upcoming_reservations = [];
}
?>


























































































































































































































































































<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payment Dashboard</title>
</head>
<body>
    <h1>Payment Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($customer_name); ?>!</p>

    <!-- summary main -->
    <div>
        <h2>Payment Summary</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>Total Payments Made</th>
                <th>Total Amount Paid</th>
                <th>Pending Amount</th>
                <th>First Payment</th>
                <th>Last Payment</th>
            </tr>
            <tr>
                <td align="center"><?php echo $summary['total_payments']; ?></td>
                <td align="right">₱<?php echo number_format($summary['total_paid'], 2); ?></td>
                <td align="right">₱<?php echo number_format($summary['pending_amount'], 2); ?></td>
                <td align="center">
                    <?php echo $summary['first_payment_date'] ? date('M d, Y', strtotime($summary['first_payment_date'])) : 'N/A'; ?>
                </td>
                <td align="center">
                    <?php echo $summary['last_payment_date'] ? date('M d, Y', strtotime($summary['last_payment_date'])) : 'N/A'; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- pendingmain -->
    <div>
        <h2>Pending Payments</h2>
        <?php if (empty($pending_payments)): ?>
            <p>No pending payments.</p>
        <?php else: ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Payment ID</th>
                    <th>Reservation</th>
                    <th>Payment Bundle</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Payment Mode</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($pending_payments as $payment): ?>
                <tr>
                    <td align="center"><?php echo $payment['payment_id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($payment['room_type']); ?><br>
                        <?php echo date('M d, Y', strtotime($payment['checkin_date'])); ?> - 
                        <?php echo date('M d, Y', strtotime($payment['checkout_date'])); ?>
                    </td>
                    <td><?php echo $payment['payment_bundle'] ?? 'Deposit'; ?></td>
                    <td align="right">₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                    <td align="center">
                        <?php echo isset($payment['due_date']) ? date('M d, Y', strtotime($payment['due_date'])) : 'N/A'; ?>
                    </td>
                    <td><?php echo $payment['mode_of_payment'] ?? 'Not Specified'; ?></td>
                    <td>
                        <a href="pay_now.php?payment_id=<?php echo $payment['payment_id']; ?>">Pay Now</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- umain -->
    <div>
        <h2>Upcoming Reservations & Payment Status</h2>
        <?php if (empty($upcoming_reservations)): ?>
            <p>No upcoming reservations.</p>
        <?php else: ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Reservation ID</th>
                    <th>Room Type</th>
                    <th>Check-in Date</th>
                    <th>Check-out Date</th>
                    <th>Total Amount</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($upcoming_reservations as $reservation): ?>
                <tr>
                    <td align="center"><?php echo $reservation['reservation_id']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['room_type']); ?></td>
                    <td align="center"><?php echo date('M d, Y', strtotime($reservation['checkin_date'])); ?></td>
                    <td align="center"><?php echo date('M d, Y', strtotime($reservation['checkout_date'])); ?></td>
                    <td align="right">₱<?php echo number_format($reservation['total_amount'], 2); ?></td>
                    <td align="right">₱<?php echo number_format($reservation['paid_amount'], 2); ?></td>
                    <td align="right">₱<?php echo number_format($reservation['balance'], 2); ?></td>
                    <td align="center">
                        <?php if ($reservation['payment_status'] == 'Paid'): ?>
                            <span style="color: green;">✓ Fully Paid</span>
                        <?php elseif ($reservation['payment_status'] == 'Partial'): ?>
                            <span style="color: orange;">● Partial</span>
                        <?php else: ?>
                            <span style="color: red;">✗ Unpaid</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($reservation['balance'] > 0): ?>
                            <a href="make_payment.php?reservation_id=<?php echo $reservation['reservation_id']; ?>">Pay Now</a>
                        <?php else: ?>
                            <a href="view_reservation.php?id=<?php echo $reservation['reservation_id']; ?>">View</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- Rmain -->
    <div>
        <h2>Recent Payments</h2>
        <?php if (empty($completed_payments)): ?>
            <p>No payment history found.</p>
        <?php else: ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Date</th>
                    <th>Reservation</th>
                    <th>Amount</th>
                    <th>Payment Mode</th>
                    <th>Status</th>
                    <th>Receipt</th>
                </tr>
                <?php foreach ($completed_payments as $payment): ?>
                <tr>
                    <td align="center"><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                    <td>
                        <?php echo htmlspecialchars($payment['room_type']); ?><br>
                        <?php echo date('M d, Y', strtotime($payment['checkin_date'])); ?>
                    </td>
                    <td align="right">₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                    <td><?php echo $payment['mode_of_payment'] ?? 'N/A'; ?></td>
                    <td align="center">
                        <span style="color: green;">✓ Completed</span>
                    </td>
                    <td>
                        <a href="receipt.php?payment_id=<?php echo $payment['payment_id']; ?>">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- 2 -->
    <div>
        <h2>Quick Links</h2>
        <ul>
            <li><a href="make_payment.php">Make a Payment</a></li>
            <li><a href="payment_history.php">View Payment History</a></li>
            <li><a href="my_reservations.php">My Reservations</a></li>
            <li><a href="profile.php">My Profile</a></li>
        </ul>
    </div>

    <!-- 3-->
    <div>
        <h2>Available Payment Methods</h2>
        <ul>
            <li><strong>GCash:</strong> Send to 0917-123-4567</li>
            <li><strong>Bank Transfer:</strong> BDO Account: 123-456-7890</li>
            <li><strong>Cash:</strong> Pay at resort reception</li>
            <li><strong>Credit/Debit Card:</strong> Available at resort</li>
        </ul>
    </div>

    <!-- 4 -->
    <div>
        <h2>Need Help?</h2>
        <p>Email: payments@resort.com</p>
        <p>Phone: (02) 1234-5678</p>
        <p><a href="contact.php">Contact Suppklgsajfaskjgfkjafkasgfkasjfhaskjfaskfjhort</a></p>
    </div>
</body>
</html>