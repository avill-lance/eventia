<?php
date_default_timezone_set('Asia/Manila'); // Adjust to your database timezone
// ### Establish Session ###
include  __DIR__ . "/session.php"; // Comment out if you don't have this file

// Establish database connection
include  __DIR__ . "/../database/config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $otp_entered = $_POST['otp'] ?? '';
    
    // Validate inputs
    if (empty($email) || empty($otp_entered) || !is_numeric($otp_entered)) {
        echo "invalid";
        exit;
    }
    
    // First, let's check what's actually in the database for debugging
    $debug_query = $conn->prepare("SELECT otp, otp_expiry, NOW() as current_db_time FROM tbl_users WHERE email = ?");
    $debug_query->bind_param("s", $email);
    $debug_query->execute();
    $debug_result = $debug_query->get_result();
    
    if ($debug_result->num_rows > 0) {
        $user_data = $debug_result->fetch_assoc();
        error_log("Debug - OTP in DB: " . $user_data['otp'] . ", Expiry: " . $user_data['otp_expiry'] . ", Current DB Time: " . $user_data['current_db_time']);
    }
    $debug_query->close();
    
    // Check if OTP matches and is not expired (using database time)
    $check_otp = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
    $check_otp->bind_param("si", $email, $otp_entered);
    $check_otp->execute();
    $result = $check_otp->get_result();
    
    if ($result->num_rows > 0) {
        // OTP verified - activate user account
        $update_user = $conn->prepare("UPDATE tbl_users SET status = 'active', otp = NULL, otp_expiry = NULL WHERE email = ?");
        $update_user->bind_param("s", $email);
        
        if ($update_user->execute()) {
            echo "verified";
        } else {
            echo "error";
        }
        $update_user->close();
    } else {
        // Check if OTP exists but expired
        $check_expired = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? AND otp = ?");
        $check_expired->bind_param("si", $email, $otp_entered);
        $check_expired->execute();
        $expired_result = $check_expired->get_result();
        
        if ($expired_result->num_rows > 0) {
            error_log("OTP expired - User entered: $otp_entered, DB has matching OTP but it's expired");
            echo "expired";
        } else {
            error_log("Invalid OTP - User entered: $otp_entered, No matching OTP found in DB");
            echo "invalid";
        }
        $check_expired->close();
    }
    
    $check_otp->close();
    $conn->close();
}
?>