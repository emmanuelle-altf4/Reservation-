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

$payment_id = $_GET['payment_id'] ?? 0;

if (!$payment_id) {
    die("Invalid payment ID.");
}


$payment_query = "SELECT 
                    p.*,
                    r.checkin_date,
                    r.checkout_date,
                    r.room_type,
                    r.reservation_id,
                    u.customer_name,
                    u.customer_email
                  FROM payments p
                  JOIN customerreservation r ON p.reservation_id = r.reservation_id
                  JOIN user u ON p.user_id = u.user_id
                  WHERE p.payment_id = ? AND p.user_id = ? AND p.payment_status = 'Pending'";
$payment_stmt = $pdo->prepare($payment_query);
$payment_stmt->execute([$payment_id, $user_id]);
$payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Payment not found or not pending.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_mode = $_POST['payment_mode'];
    $transaction_reference = $_POST['transaction_reference'];
    $payment_date = $_POST['payment_date'];
    $amount_paid = $_POST['amount_paid'];
    $proof_image = $_FILES['proof_image'];
    
 
    if ($amount_paid < $payment['amount_paid']) {
        $error = "Amount paid cannot be less than the required amount.";
    } else {
      
        $proof_path = '';
        if ($proof_image['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'payment_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($proof_image['name'], PATHINFO_EXTENSION);
            $filename = 'payment_' . $payment_id . '_' . time() . '.' . $file_extension;
            $proof_path = $upload_dir . $filename;
            
            if (move_uploaded_file($proof_image['tmp_name'], $proof_path)) {
            
                $update_query = "UPDATE payments SET 
                                mode_of_payment = ?,
                                transaction_reference = ?,
                                payment_date = ?,
                                amount_paid = ?,
                                proof_image = ?,
                                payment_status = 'Completed',
                                updated_at = NOW()
                                WHERE payment_id = ?";
                
                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->execute([
                    $payment_mode,
                    $transaction_reference,
                    $payment_date,
                    $amount_paid,
                    $proof_path,
                    $payment_id
                ]);
                
                $success = "Payment submitted successfully! Our staff will verify it shortly.";
            } else {
                $error = "Failed to upload proof image.";
            }
        } else {
            $error = "Please upload payment proof.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Now - Resort Reservation</title>
</head>
<body>
    <h1>paki complete ang imong payment</h1>
    
    <?php if (isset($success)): ?>
        <div style="color: green; padding: 10px; border: 1px solid green; margin: 10px 0;">
            <?php echo $success; ?>
            <p><a href="payment_dashboard.php">Back to Payment Dashboard</a></p>
        </div>
    <?php else: ?>
    

    <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
        <h3>Payment Details</h3>
        <table>
            <tr>
                <td><strong>Payment ID:</strong></td>
                <td><?php echo $payment['payment_id']; ?></td>
            </tr>
            <tr>
                <td><strong>Reservation:</strong></td>
                <td><?php echo $payment['room_type']; ?> (ID: <?php echo $payment['reservation_id']; ?>)</td>
            </tr>
            <tr>
                <td><strong>Check-in:</strong></td>
                <td><?php echo date('M d, Y', strtotime($payment['checkin_date'])); ?></td>
            </tr>
            <tr>
                <td><strong>Amount Due:</strong></td>
                <td style="color: red; font-weight: bold;">
                    ₱<?php echo number_format($payment['amount_paid'], 2); ?>
                </td>
            </tr>
            <tr>
                <td><strong>Due Date:</strong></td>
                <td><?php echo date('M d, Y', strtotime($payment['due_date'])); ?></td>
            </tr>
        </table>
    </div>
    

    <form method="POST" enctype="multipart/form-data">
        <?php if (isset($error)): ?>
            <div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <h3>Payment Information</h3>
        
        <div>
            <label>Payment Mode:</label><br>
            <select name="payment_mode" required>
                <option value="">Select Payment Method</option>
                <option value="GCash">GCash</option>
                <option value="Maya">Maya</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Debit Card">Debit Card</option>
                <option value="Cash">Cash (at resort)</option>
            </select>
        </div>
        
        <div>
            <label>Transaction Reference/Number:</label><br>
            <input type="text" name="transaction_reference" required 
                   placeholder="e.g., GCash Reference Number, Bank Transaction ID">
        </div>
        
        <div>
            <label>Amount Paid:</label><br>
            <input type="number" name="amount_paid" step="0.01" min="<?php echo $payment['amount_paid']; ?>" 
                   value="<?php echo $payment['amount_paid']; ?>" required>
        </div>
        
        <div>
            <label>Payment Date:</label><br>
            <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div>
            <label>Upload Payment Proof (Screenshot/Receipt):</label><br>
            <input type="file" name="proof_image" accept="image/*" required>
            <small>Upload screenshot of successful transaction or receipt photo</small>
        </div>
        
        <div style="margin: 20px 0;">
            <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;">
                Submit Payment
            </button>
            <a href="payment_dashboard.php" style="margin-left: 10px;">Cancel</a>
        </div>
    </form>
    
 
    <div style="background: #f5f5f5; padding: 15px; margin: 20px 0;">
        <h3>Payment Instructions</h3>
        
        <h4>For GCash:</h4>
        <ol>
            <li>Open GCash app</li>
            <li>Go to "Send Money"</li>
            <li>Enter mobile number: <strong>0917-123-4567</strong></li>
            <li>Enter the exact amount: <strong>₱<?php echo number_format($payment['amount_paid'], 2); ?></strong></li>
            <li>Use <strong>Reservation ID: <?php echo $payment['reservation_id']; ?></strong> as reference</li>
            <li>Take screenshot of successful transaction</li>
            <li>Upload screenshot above</li>
        </ol>
        
        <h4>For Bank Transfer:</h4>
        <ul>
            <li><strong>Bank:</strong> BDO (Banco De Oro)</li>
            <li><strong>Account Name:</strong> Resort Paradise Inc.</li>
            <li><strong>Account Number:</strong> 123-456-7890</li>
            <li><strong>Reference:</strong> Reservation ID: <?php echo $payment['reservation_id']; ?></li>
            <li><strong>Amount:</strong> ₱<?php echo number_format($payment['amount_paid'], 2); ?></li>
        </ul>
        
        <p style="color: #666; font-style: italic;">
            Note: Your reservation will be confirmed once payment is verified by our staff.
            Verification usually takes 1-2 business days.
        </p>
    </div>
    
    <?php endif; ?>
</body>
</html>