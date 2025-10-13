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
                        $mail->Body    = "
                            <h2>Email Verification</h2>
                            <p>Your OTP verification code is: <strong style='font-size: 24px;'>{$otp}</strong></p>
                            <p>This code will expire in 10 minutes.</p>
                            <p>If you didn't request this code, please ignore this email.</p>
                            <br>
                            <p>Best regards,<br>Eventia Team</p>
                        ";
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

