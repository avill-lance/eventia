<?php
date_default_timezone_set('Asia/Manila');
include  __DIR__ . "/session.php";
include  __DIR__ . "/../database/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $otp_entered = $_POST['otp'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    
    // Validate inputs
    if (empty($email) || empty($otp_entered) || !is_numeric($otp_entered)) {
        echo "invalid";
        exit;
    }
    
    // Check if OTP matches and is not expired
    $check_otp = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
    $check_otp->bind_param("si", $email, $otp_entered);
    $check_otp->execute();
    $result = $check_otp->get_result();
    
    if ($result->num_rows > 0) {
        // OTP verified
        $update_user = $conn->prepare("UPDATE tbl_users SET status = 'active', otp = NULL, otp_expiry = NULL WHERE email = ?");
        $update_user->bind_param("s", $email);
        
        if ($update_user->execute()) {
            // Trim and validate purpose
            $purpose = trim($purpose);

            // Debug: Log all POST data
            error_log("POST data received:");
            foreach ($_POST as $key => $value) {
                error_log("$key: '$value' (length: " . strlen($value) . ")");
            }
            
            // Return specific response based on purpose
            if($purpose === 'changepassword') {
                echo 'changepassword';
            } else {
                echo 'verified';
            }
        } 
        $update_user->close();
    } else {
        $check_expired = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? AND otp = ?");
        $check_expired->bind_param("si", $email, $otp_entered);
        $check_expired->execute();
        $expired_result = $check_expired->get_result();
        
        if ($expired_result->num_rows > 0) {
            echo "expired";
        } else {
            echo "invalid";
        }
        $check_expired->close();
    }
    
    $check_otp->close();
    $conn->close();
}
?>