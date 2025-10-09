<?php
date_default_timezone_set('Asia/Manila'); // Adjust to your database timezone

// Establish database connection
include  __DIR__ . "/../database/config.php";

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Path to autoloader
$autoloaderPath = __DIR__ . '/../PHPmailer/vendor/autoload.php';
if (file_exists($autoloaderPath)) {
    require $autoloaderPath;
} else {
    echo "autoload_error";
    exit;
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $email = $_POST['email'] ?? '';
    
    if(empty($email)) {
        echo "invalid_email";
        exit;
    }
    
    // Generate new OTP and expiry
    $new_otp = rand(1000, 9999);
    $new_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Update OTP in database
    $update_otp = $conn->prepare("UPDATE tbl_users SET otp = ?, otp_expiry = ? WHERE email = ?");
    $update_otp->bind_param("iss", $new_otp, $new_expiry, $email);
    
    if($update_otp->execute()) {
        // Send OTP email
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'verifyeventia@gmail.com';
            $mail->Password   = 'jddyunjctayldtdd';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom('verifyeventia@gmail.com', 'Eventia');
            $mail->addAddress($email);
            $mail->addReplyTo('verifyeventia@gmail.com', 'Eventia Support');

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Eventia Verification Code';
            $mail->Body    = "
                <h2>Email Verification</h2>
                <p>Your new OTP verification code is: <strong style='font-size: 24px;'>{$new_otp}</strong></p>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this code, please ignore this email.</p>
                <br>
                <p>Best regards,<br>Eventia Team</p>
            ";
            $mail->AltBody = "Your new OTP verification code is: {$new_otp}. This code will expire in 10 minutes.";

            $mail->send();
            echo "resent";
        } catch (Exception $e) {
            echo "mail_error";
        }
    } else {
        echo "database_error";
    }
    
    $update_otp->close();
    $conn->close();
}
?>