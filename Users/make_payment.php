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


$reservation_id = $_GET['reservation_id'] ?? 0;


$reservations_query = "SELECT 
                         r.reservation_id,
                         r.checkin_date,
                         r.checkout_date,
                         r.room_type,
                         r.status as reservation_status,
                         (SELECT SUM(amount_paid) FROM payments WHERE reservation_id = r.reservation_id) as paid_amount
                       FROM customerreservation r
                       WHERE r.user_id = ? 
                         AND r.status IN ('Confirmed', 'Pending')
                       ORDER BY r.checkin_date DESC";
$reservations_stmt = $pdo->prepare($reservations_query);
$reservations_stmt->execute([$user_id]);
$reservations = $reservations_stmt->fetchAll(PDO::FETCH_ASSOC);


foreach ($reservations as &$reservation) {
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
    
    $reservation['paid_amount'] = $reservation['paid_amount'] ?? 0;
    $reservation['balance'] = $reservation['total_amount'] - $reservation['paid_amount'];
    $reservation['payment_status'] = $reservation['balance'] <= 0 ? 'Paid' : 
                                    ($reservation['paid_amount'] > 0 ? 'Partial' : 'Unpaid');
}
unset($reservation);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $payment_bundle = $_POST['payment_bundle'];
    $amount_paid = $_POST['amount_paid'];
    $payment_mode = $_POST['payment_mode'];
    $notes = $_POST['notes'] ?? '';
    
    $reservation_query = "SELECT 
                            r.*,
                            u.user_id,
                            u.customer_email
                          FROM customerreservation r
                          JOIN user u ON r.user_id = u.user_id
                          WHERE r.reservation_id = ? AND r.user_id = ?";
    $reservation_stmt = $pdo->prepare($reservation_query);
    $reservation_stmt->execute([$reservation_id, $user_id]);
    $reservation = $reservation_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reservation) {
        switch ($reservation['room_type']) {
            case 'Standard Villa':
                $total_amount = 15000.00;
                break;
            case 'Deluxe Two-Bedroom Villa':
                $total_amount = 25000.00;
                break;
            default:
                $total_amount = 20000.00;
        }
        
        $existing_payments_query = "SELECT 
                                      SUM(amount_paid) as total_paid,
                                      MAX(new_balance) as last_balance
                                    FROM payments 
                                    WHERE reservation_id = ? AND user_id = ?";
        $existing_stmt = $pdo->prepare($existing_payments_query);
        $existing_stmt->execute([$reservation_id, $user_id]);
        $existing_data = $existing_stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_paid_so_far = $existing_data['total_paid'] ?? 0;
        $previous_balance = $total_amount - $total_paid_so_far;
        $new_balance = $previous_balance - $amount_paid;
        $insert_query = "INSERT INTO payments (
            reservation_id, user_id, payment_bundle, mode_of_payment,
            total_amount, amount_paid, previous_balance, new_balance,
            payment_status, payment_notes, due_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, DATE_ADD(NOW(), INTERVAL 3 DAY))";
        
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute([
            $reservation_id, $user_id, $payment_bundle, $payment_mode,
            $total_amount, $amount_paid, $previous_balance, $new_balance,
            $notes
        ]);
        
        $payment_id = $pdo->lastInsertId();
        $success = "Payment request created successfully! Payment ID: $payment_id";
    } else {
        $error = "Reservation not found.";
    }
}
?>








           
























































<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - Resort Reservation</title>
</head>
<body>
    <h1>Make a Payment</h1>
    <p>Welcome, <?php echo htmlspecialchars($customer_name); ?>!</p>
    
    <?php if (isset($success)): ?>
        <div style="color: green; padding: 10px; border: 1px solid green; margin: 10px 0;">
            <?php echo $success; ?>
            <p>
                <a href="pay_now.php?payment_id=<?php echo $payment_id; ?>">Proceed to Payment</a> |
                <a href="payment_dashboard.php">Back to Dashboard</a>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    

    <div>
        <h2>Your Reservations</h2>
        <?php if (empty($reservations)): ?>
            <p>No reservations found.</p>
        <?php else: ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Reservation ID</th>
                    <th>Room Type</th>
                    <th>Check-in Date</th>
                    <th>Check-out Date</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td align="center"><?php echo $reservation['reservation_id']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['room_type']); ?></td>
                    <td align="center"><?php echo date('M d, Y', strtotime($reservation['checkin_date'])); ?></td>
                    <td align="center"><?php echo date('M d, Y', strtotime($reservation['checkout_date'])); ?></td>
                    <td align="right">₱<?php echo number_format($reservation['total_amount'], 2); ?></td>
                    <td align="right">₱<?php echo number_format($reservation['paid_amount'], 2); ?></td>
                    <td align="right">
                        <?php if ($reservation['balance'] > 0): ?>
                            <span style="color: red;">₱<?php echo number_format($reservation['balance'], 2); ?></span>
                        <?php else: ?>
                            <span style="color: green;">₱0.00</span>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <?php if ($reservation['payment_status'] == 'Paid'): ?>
                            <span style="color: green;">PAID</span>
                        <?php elseif ($reservation['payment_status'] == 'Partial'): ?>
                            <span style="color: orange;">● UTANG JOKE PARTIAL</span>
                        <?php else: ?>
                            <span style="color: red;">SWIMMING GUSTO BAYAD AYAW?</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($reservation['balance'] > 0): ?>
                            <a href="make_payment.php?reservation_id=<?php echo $reservation['reservation_id']; ?>">
                                Make Payment
                            </a>
                        <?php else: ?>
                            <span style="color: #666;">Fully Paid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    
 
    <?php if ($reservation_id): ?>
        <?php 
    
        $selected_reservation = null;
        foreach ($reservations as $res) {
            if ($res['reservation_id'] == $reservation_id) {
                $selected_reservation = $res;
                break;
            }
        }
        
        if ($selected_reservation && $selected_reservation['balance'] > 0):
        ?>
        <div style="border: 2px solid #007bff; padding: 20px; margin: 20px 0;">
            <h2>Make Payment for Reservation #<?php echo $selected_reservation['reservation_id']; ?></h2>
            
            <div style="margin: 15px 0;">
                <strong>Room Type:</strong> <?php echo htmlspecialchars($selected_reservation['room_type']); ?><br>
                <strong>Check-in:</strong> <?php echo date('M d, Y', strtotime($selected_reservation['checkin_date'])); ?><br>
                <strong>Check-out:</strong> <?php echo date('M d, Y', strtotime($selected_reservation['checkout_date'])); ?><br>
                <strong>Total Amount:</strong> ₱<?php echo number_format($selected_reservation['total_amount'], 2); ?><br>
                <strong>Already Paid:</strong> ₱<?php echo number_format($selected_reservation['paid_amount'], 2); ?><br>
                <strong>Remaining Balance:</strong> <span style="color: red; font-weight: bold;">
                    ₱<?php echo number_format($selected_reservation['balance'], 2); ?>
                </span>
            </div>
            <!-- popup to nakalimutan kona basta payment ata sa baba -->
            <form method="POST">
                <input type="hidden" name="reservation_id" value="<?php echo $selected_reservation['reservation_id']; ?>">
                
                <div style="margin: 10px 0;">
                    <label>Payment Type:</label><br>
                    <select name="payment_bundle" required>
                        <option value="">Select Payment Type</option>
                        <option value="Full Payment">Full Payment</option>
                        <option value="Deposit (50%)">Deposit (50%)</option>
                        <option value="Deposit (30%)">Deposit (30%)</option>
                        <option value="Balance Payment">Balance Payment</option>
                        <option value="Partial Payment">Partial Payment</option>
                    </select>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Amount to Pay (₱):</label><br>
                    <input type="number" name="amount_paid" step="0.01" min="1" 
                           max="<?php echo $selected_reservation['balance']; ?>" 
                           value="<?php echo $selected_reservation['balance']; ?>" required>
                           <!-- dili man nagana corny ata gagawin ko mamaya-->
                    <small>Maximum: ₱<?php echo number_format($selected_reservation['balance'], 2); ?></small>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Payment Method:</label><br>
                    <select name="payment_mode" required>
                        <option value="">Select Method</option>
                        <option value="GCash">GCash</option>
                        <option value="Maya">Maya</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="Cash">Cash (at resort)</option>
                    </select>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Notes (Optional):</label><br>
                    <textarea name="notes" rows="3" placeholder="arte mo papagawain mopa kame ng design sa resort?"></textarea>
                </div>
                
                <div style="margin: 20px 0;">
                    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">
                        Create Payment Request
                    </button>
                    <a href="make_payment.php" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div style="margin-top: 30px;">
        <a href="payment_dashboard.php">← Back to Payment Dashboard</a>
    </div>
</body>
</html>