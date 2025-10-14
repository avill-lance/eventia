<?php
date_default_timezone_set('Asia/Manila'); // Adjust to your database timezone
// ### Establish Database Connection ###
include   __DIR__ . "/../database/config.php";

// ### Establish Database Connection ###
include  "session.php";

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Path to autoloader
$autoloaderPath = __DIR__ . "/../vendor/autoload.php";

if (file_exists($autoloaderPath)) {
    require $autoloaderPath;
} else {
    die("Autoloader not found at: " . $autoloaderPath);
}

function email($new_otp){
    return"<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Eventia Verification Code</title>
    <style>
        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .email-body {
            padding: 30px;
        }
        
        .otp-code {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
            border: 1px dashed #dee2e6;
        }
        
        .otp-code strong {
            font-size: 32px;
            letter-spacing: 8px;
            color: #2575fc;
            font-weight: 700;
        }
        
        .expiry-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 12px 15px;
            margin: 20px 0;
            color: #856404;
        }
        
        .footer {
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        
        .disclaimer {
            font-size: 12px;
            color: #868e96;
            margin-top: 15px;
            line-height: 1.4;
        }
        
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 20px;
            }
            
            .otp-code strong {
                font-size: 26px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header'>
            <h1>Eventia</h1>
        </div>
        
        <div class='email-body'>
            <h2 style='margin-top: 0;'>Change Password</h2>
            <p>Hello,</p>
            <p>Thank you for using Eventia. To complete your verification, please use the following One-Time Password (OTP):</p>
            
            <div class='otp-code'>
                <strong>$new_otp</strong>
            </div>
            
            <div class='expiry-notice'>
                <p><strong>Important:</strong> This verification code will expire in 10 minutes.</p>
            </div>
            
            <p>If you didn't request this code, please ignore this email. Your account security is important to us.</p>
            
            <p>Best regards,<br><strong>Eventia Team</strong></p>
        </div>
        
        <div class='footer'>
            <p>&copy; 2023 Eventia. All rights reserved.</p>
            <p class='disclaimer'>
                This email was sent to you because you requested a verification code for your Eventia account. 
                Please do not reply to this email as it is automatically generated.
            </p>
        </div>
    </div>
</body>
</html>";
}

// ### Validates registration process ###
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $first_name = htmlspecialchars($_POST['firstname'])??'';
    $last_name = htmlspecialchars($_POST['lastname'])??'';
    $email = htmlspecialchars($_POST['email'])??'';
    $phone = htmlspecialchars($_POST['phone'])??'';
    $city = htmlspecialchars($_POST['city'])??'';
    $zip = htmlspecialchars($_POST['zip'])??'';
    $address = htmlspecialchars($_POST['address'])??'';
    $password = htmlspecialchars($_POST['password'])??'';
    $confirm_password = htmlspecialchars($_POST['confirmPassword'])??'';
    $otp = rand(1000, 9999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $check_email= $conn->prepare("SELECT * FROM tbl_users where email=?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    // ### Check if email already exists ###
    if($check_email->num_rows>0){
        echo "existing";
    }
    // ### If none exists ###
    else{
        // ### Checks if there are empty fields ###
        if(empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($city) || empty($zip) || empty($address) || empty($password) ||empty($confirm_password)){
            echo "required";
        }
        // ### If all have values ###
        else{
            // ### Check if password and confirm password does not have the same value ###
            if($password != $confirm_password){
            echo "differentpassword";
            }
            // ### If every condition is met, insert the data into database ###
            else{
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert_user = $conn->prepare("INSERT INTO tbl_users (first_name, last_name, email, phone, city, zip, address, password, otp, otp_expiry) VALUES(?,?,?,?,?,?,?,?,?,?)");
                $insert_user->bind_param("ssssssssis", $first_name,$last_name,$email,$phone,$city,$zip,$address,$hash,$otp,$otp_expiry);
                
                if($insert_user->execute()){
                    $_SESSION['pending_verification'] = [
                        'email' => $email,  
                        'created_at' => time(),
                        'expires_at' => time() + 600, // 10 minutes
                        'expires_at_readable' => date('Y-m-d H:i:s', time() + 600) // For debugging
                    ];
                    
                    // Set verification cookie
                    setcookie('pending_verification', 'true', time() + 600, '/');
                    // Send OTP email after successful registration
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
                        $mail->Body    = email($new_otp);
                        $mail->AltBody = "Your OTP verification code is: {$otp}. This code will expire in 10 minutes.";

                        if($mail->send()) {
                            // Return JSON response with email
                            echo json_encode(['status' => 'added', 'email' => $email]);
                        } else {
                            echo "mail_error";
                        }
                    } catch (Exception $e) {
                        echo "mail_error";
                    }
                }
                else{
                    echo "error";
                }
            }
        }
    }
}
?>

