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

// Get all reservations for dropdown
$reservations_query = "SELECT 
                         r.reservation_id,
                         r.checkin_date,
                         r.checkout_date,
                         r.room_type,
                         r.status,
                         u.customer_name,
                         u.customer_email,
                         (SELECT SUM(amount_paid) FROM payments WHERE reservation_id = r.reservation_id) as paid_amount
                       FROM customerreservation r
                       JOIN user u ON r.user_id = u.user_id
                       WHERE r.status IN ('Confirmed', 'Pending')
                       ORDER BY r.checkin_date DESC";
$reservations_stmt = $pdo->query($reservations_query);
$reservations = $reservations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate balances for each reservation
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
}
unset($reservation);

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $payment_bundle = $_POST['payment_bundle'];
    $mode_of_payment = $_POST['mode_of_payment'];
    $amount_paid = $_POST['amount_paid'];
    $transaction_reference = $_POST['transaction_reference'] ?? '';
    $payment_date = $_POST['payment_date'];
    $notes = $_POST['notes'] ?? '';
    
    try {
      
        if ($reservation_id) {
        
            $res_query = "SELECT r.*, u.user_id FROM customerreservation r
                         JOIN user u ON r.user_id = u.user_id
                         WHERE r.reservation_id = ?";
            $res_stmt = $pdo->prepare($res_query);
            $res_stmt->execute([$reservation_id]);
            $reservation = $res_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                throw new Exception("Reservation not found.");
            }
            
            $user_id = $reservation['user_id'];
            
    
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
            
    
            $existing_query = "SELECT 
                                 SUM(amount_paid) as total_paid,
                                 MAX(new_balance) as last_balance
                               FROM payments 
                               WHERE reservation_id = ?";
            $existing_stmt = $pdo->prepare($existing_query);
            $existing_stmt->execute([$reservation_id]);
            $existing_data = $existing_stmt->fetch(PDO::FETCH_ASSOC);
            
            $total_paid_so_far = $existing_data['total_paid'] ?? 0;
            $previous_balance = $total_amount - $total_paid_so_far;
            $new_balance = $previous_balance - $amount_paid;
        } else {
           
            $user_id = null; 
            $total_amount = $amount_paid; //assumero na full
            $previous_balance = $total_amount;
            $new_balance = 0;
            $reservation_id = null;
        }
        
        // sql insert payment
        $insert_query = "INSERT INTO payments (
            reservation_id, user_id, payment_bundle, mode_of_payment,
            total_amount, amount_paid, previous_balance, new_balance,
            transaction_reference, payment_date, payment_status,
            payment_notes, processed_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completed', ?, ?)";
        
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute([
            $reservation_id,
            $user_id,
            $payment_bundle,
            $mode_of_payment,
            $total_amount,
            $amount_paid,
            $previous_balance,
            $new_balance,
            $transaction_reference,
            $payment_date,
            $notes,
            $staff_id
        ]);
        
        $payment_id = $pdo->lastInsertId();
        
        $message = "Payment added successfully! Payment ID: $payment_id";
        $message_type = "success";
        
    } catch (Exception $e) {
        $message = "Error adding payment: " . $e->getMessage();
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Manual Payment - Staff</title>
</head>
<body>

<!-- DATABASE PULL  -->
    <h1>Add Manual Payment</h1>
    <p>Staff: <?php echo htmlspecialchars($staff_name); ?> | <a href="staff_payments.php">← Back to Payments</a></p>
    
    <?php if ($message): ?>
        <div style="color: <?php echo $message_type == 'success' ? 'green' : 'red'; ?>; 
                    padding: 10px; border: 1px solid; margin: 10px 0;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div style="display: flex; gap: 20px;">
        <!-- Left Column: Form -->
        <div style="flex: 1;">
            <form method="POST">
                <h3>Payment Information</h3>
                
                <div style="margin: 10px 0;">
                    <label>Select Reservation (Optional):</label><br>
                    <select name="reservation_id" id="reservation_select" onchange="updateReservationDetails()">
                        <option value="">-- Walk-in Customer (No Reservation) --</option>
                        <?php foreach ($reservations as $res): ?>
                        <option value="<?php echo $res['reservation_id']; ?>"
                                data-customer="<?php echo htmlspecialchars($res['customer_name']); ?>"
                                data-email="<?php echo htmlspecialchars($res['customer_email']); ?>"
                                data-room="<?php echo htmlspecialchars($res['room_type']); ?>"
                                data-total="<?php echo $res['total_amount']; ?>"
                                data-paid="<?php echo $res['paid_amount']; ?>"
                                data-balance="<?php echo $res['balance']; ?>">
                            Reservation #<?php echo $res['reservation_id']; ?> - 
                            <?php echo htmlspecialchars($res['customer_name']); ?> - 
                            <?php echo htmlspecialchars($res['room_type']); ?> - 
                            Balance: ₱<?php echo number_format($res['balance'], 2); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Customer Name:</label><br>
                    <input type="text" name="customer_name" id="customer_name" 
                           placeholder="Enter customer name" required>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Customer Email:</label><br>
                    <input type="email" name="customer_email" id="customer_email" 
                           placeholder="customer@example.com">
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Payment Type:</label><br>
                    <select name="payment_bundle" required>
                        <option value="">Select Payment Type</option>
                        <option value="Full Payment">Full Payment</option>
                        <option value="Deposit (50%)">Deposit (50%)</option>
                        <option value="Deposit (30%)">Deposit (30%)</option>
                        <option value="Balance Payment">Balance Payment</option>
                        <option value="Partial Payment">Partial Payment</option>
                        <option value="Refund">Refund</option>
                        <option value="Service Fee">Service Fee</option>
                    </select>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Payment Method:</label><br>
                    <select name="mode_of_payment" required>
                        <option value="">Select Method</option>
                        <option value="Cash">Cash</option>
                        <option value="GCash">GCash</option>
                        <option value="Maya">Maya</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                    </select>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Amount Paid (₱):</label><br>
                    <input type="number" name="amount_paid" id="amount_paid" 
                           step="0.01" min="1" required>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Transaction Reference:</label><br>
                    <input type="text" name="transaction_reference" 
                           placeholder="Receipt number, GCash reference, etc.">
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Payment Date:</label><br>
                    <input type="datetime-local" name="payment_date" 
                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </div>
                
                <div style="margin: 10px 0;">
                    <label>Notes:</label><br>
                    <textarea name="notes" rows="3" placeholder="Any special instructions..."></textarea>
                </div>
                
                <div style="margin: 20px 0;">
                    <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;">
                        Add Payment
                    </button>
                    <a href="staff_payments.php" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        
        <!-- Right Column: Reservation Details -->
        <div style="flex: 1; border: 1px solid #ddd; padding: 15px;">
            <h3>Reservation Details</h3>
            <div id="reservation_details">
                <p>Select a reservation to see details, or leave empty for walk-in customer.</p>
            </div>
            
            <h3>Recent Reservations Needing Payment</h3>
            <table border="1" cellpadding="8" cellspacing="0" width="100%">
                <tr>
                    <th>Reservation ID</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Balance</th>
                </tr>
                <?php 
                $count = 0;
                foreach ($reservations as $res):
                    if ($res['balance'] > 0 && $count < 5):
                        $count++;
                ?>
                <tr>
                    <td align="center"><?php echo $res['reservation_id']; ?></td>
                    <td><?php echo htmlspecialchars($res['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($res['room_type']); ?></td>
                    <td align="right" style="color: red;">
                        ₱<?php echo number_format($res['balance'], 2); ?>
                    </td>
                </tr>
                <?php 
                    endif;
                endforeach; 
                
                if ($count == 0): ?>
                <tr>
                    <td colspan="4" align="center">No reservations with outstanding balance</td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <script>
    function updateReservationDetails() {
        var select = document.getElementById('reservation_select');
        var selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            var customerName = selectedOption.getAttribute('data-customer');
            var customerEmail = selectedOption.getAttribute('data-email');
            var roomType = selectedOption.getAttribute('data-room');
            var totalAmount = selectedOption.getAttribute('data-total');
            var paidAmount = selectedOption.getAttribute('data-paid');
            var balance = selectedOption.getAttribute('data-balance');
            
            document.getElementById('customer_name').value = customerName;
            document.getElementById('customer_email').value = customerEmail;
            document.getElementById('amount_paid').max = balance;
            document.getElementById('amount_paid').value = balance;
            
            var detailsHtml = `
                <p><strong>Customer:</strong> ${customerName}</p>
                <p><strong>Email:</strong> ${customerEmail}</p>
                <p><strong>Room Type:</strong> ${roomType}</p>
                <p><strong>Total Amount:</strong> ₱${parseFloat(totalAmount).toFixed(2)}</p>
                <p><strong>Already Paid:</strong> ₱${parseFloat(paidAmount).toFixed(2)}</p>
                <p><strong>Remaining Balance:</strong> <span style="color: red; font-weight: bold;">₱${parseFloat(balance).toFixed(2)}</span></p>
            `;
            
            document.getElementById('reservation_details').innerHTML = detailsHtml;
        } else {
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_email').value = '';
            document.getElementById('amount_paid').max = '';
            document.getElementById('amount_paid').value = '';
            document.getElementById('reservation_details').innerHTML = 
                '<p>Select a reservation to see details, or leave empty for walk-in customer.</p>';
        }
    }
    </script>
</body>
</html>