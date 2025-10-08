<?php
//Establish database connection
    include  __DIR__ . "/../database/config.php";
    
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Path to autoloader in your structure
$autoloaderPath = __DIR__ . '/../PHPmailer/vendor/autoload.php';

// DEBUG: Check autoloader
echo "Looking for autoload.php at: " . $autoloaderPath . "<br>";
if (file_exists($autoloaderPath)) {
    echo "Autoloader found! Loading PHPMailer...<br>";
    require $autoloaderPath;
} else {
    echo "Autoloader NOT found! Falling back to manual requires or reinstall Composer.<br>";
    // Stop or fallback here – see Step 3 for manual
    die("Fix the autoloader first.");
}

// Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug (set to 0 in production)
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                       // SMTP server
    $mail->SMTPAuth   = true;                                   // Enable authentication
    $mail->Username   = 'verifyeventia@gmail.com';              // Username
    $mail->Password   = 'jddyunjctayldtdd';                     // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // SSL for port 465
    $mail->Port       = 465;                                    // Port

    // Recipients
    $mail->setFrom('verifyeventia@gmail.com', 'Eventia');
    $mail->addAddress('kristancharles67@gmail.com');            // Recipient
    $mail->addReplyTo('verifyeventia@gmail.com', 'Information');

    // Content
    $mail->isHTML(true);                                        // HTML format
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo '<br>Message has been sent successfully!';
} catch (Exception $e) {
    echo "<br>Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
