<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// DB config
$host = "localhost";
$dbname = "resortreservation";
$dbUser = "root";
$dbPass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $checkin_date  = $_POST['checkin'] ?? null;
    $checkout_date = $_POST['checkout'] ?? null;
    $room_type     = $_POST['room_type'] ?? null;
    $guests        = $_POST['guests'] ?? null;

    if (!$checkin_date || !$checkout_date || !$room_type || !$guests) {
        echo "Please fill in all fields.";
        exit;
    }

    // Generate OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    try {
        // Insert reservation
        $stmt = $pdo->prepare("
            INSERT INTO customerreservation 
            (user_id, customer_name, checkin_date, checkout_date, room_type, guests, status, otp_code, otp_expiry) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['customer_name'] ?? '',
            $checkin_date,
            $checkout_date,
            $room_type,
            $guests,
            $otp,
            $expiry
        ]);

        $reservation_id = $pdo->lastInsertId();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

    // Get customer email and name
    $customer_email = $_SESSION['customer_email'] ?? null;
    $customer_name  = $_SESSION['customer_name'] ?? 'Guest';

    if (!$customer_email) {
        header("Location: my_reservations.php");
        exit;
    }

    // PHPMailer
    $mail = new PHPMailer(true);
    try {
        // 1. Server Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kookyarabia06@gmail.com';   // Gmail address
        $mail->Password   = 'hssm hgvh zmal llof';      // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // 2. Recipients
        $mail->setFrom('kookyarabia06@gmail.com', 'Resort Reservations');
        $mail->addAddress($customer_email, $customer_name);

        // 3. Content
        $mail->isHTML(true);
        $mail->Subject = 'Reservation Receipt & OTP Confirmation';
        $mail->Body    = "
            <h2>Reservation Receipt</h2>
            <p>Dear " . htmlspecialchars($customer_name) . ",</p>
            <p>Your reservation has been recorded:</p>
            <ul>
                <li>Check-in: " . htmlspecialchars($checkin_date) . "</li>
                <li>Check-out: " . htmlspecialchars($checkout_date) . "</li>
                <li>Room Type: " . htmlspecialchars($room_type) . "</li>
                <li>Guests: " . htmlspecialchars($guests) . "</li>
            </ul>
            <p><strong>Your OTP Code: " . htmlspecialchars($otp) . "</strong></p>
            <p>Please enter this code within 10 minutes to confirm your reservation.</p>
            <p><a href='http://localhost/HTML/Users/confirm_reservation.php?id={$reservation_id}'>Confirm Reservation Here</a></p>
        ";
        $mail->AltBody = "Reservation details:\nCheck-in: $checkin_date\nCheck-out: $checkout_date\nRoom: $room_type\nGuests: $guests\nOTP: $otp";

        $mail->send();
        $_SESSION['flash_success'] = "Reservation created and email sent.";
    } catch (Exception $e) {
        $_SESSION['flash_error'] = "Mailer Error: " . $mail->ErrorInfo;
    }

    header("Location: my_reservations.php");
    exit;
}
?>