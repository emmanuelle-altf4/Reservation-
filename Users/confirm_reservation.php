<?php
session_start();
require 'process_reservation2.php'; // adjust path to your DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reservation_id = $_POST['reservation_id'];
    $otp_input = $_POST['otp'];

    $stmt = $pdo->prepare("SELECT otp_code, otp_expiry FROM customerreservation WHERE id = ?");
    $stmt->execute([$reservation_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($res) {
        if ($otp_input === $res['otp_code'] && strtotime($res['otp_expiry']) > time()) {
            $update = $pdo->prepare("UPDATE customerreservation SET status='Confirmed' WHERE id=?");
            $update->execute([$reservation_id]);
            echo "Reservation confirmed!";
        } else {
            echo "Invalid or expired OTP.";
        }
    }
}
?>
<form method="POST">
    <input type="hidden" name="reservation_id" value="<?php echo $_GET['id']; ?>">
    <label>Enter OTP:</label>
    <input type="text" name="otp" required>
    <button type="submit">Confirm</button>
</form>
