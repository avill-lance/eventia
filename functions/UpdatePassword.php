<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establish Session
include __DIR__ . "/session.php";

// Establish database connection
include __DIR__ . "/../database/config.php";

// Log that we reached this file
error_log("=== UpdatePassword.php accessed ===");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Get raw POST data for debugging
    $raw_post = file_get_contents("php://input");
    error_log("Raw POST data: " . $raw_post);
    
    $password = $_POST["password"] ?? '';
    $confirmPassword = $_POST["confirmPassword"] ?? '';
    $email = $_POST["email"] ?? '';

    // Debug what we received
    error_log("Email received: " . $email);
    error_log("Password received: " . (empty($password) ? "EMPTY" : "SET"));
    error_log("ConfirmPassword received: " . (empty($confirmPassword) ? "EMPTY" : "SET"));

    if(empty($password) || empty($confirmPassword)){
        error_log("Validation failed: Empty fields");
        echo "empty";
        exit;
    }
    else if($password != $confirmPassword){
        error_log("Validation failed: Passwords don't match");
        echo "different";
        exit;
    }
    else{
        error_log("Starting database operations...");
        
        // Check if email exists
        $check_email = $conn->prepare("SELECT * FROM tbl_users WHERE email = ? LIMIT 1");
        if(!$check_email){
            error_log("Prepare failed: " . $conn->error);
            echo "Error - Prepare failed";
            exit;
        }
        
        $check_email->bind_param("s", $email);
        if(!$check_email->execute()){
            error_log("Execute failed: " . $check_email->error);
            echo "Error - Execute failed";
            exit;
        }
        
        $result = $check_email->get_result();
        error_log("Number of rows found: " . $result->num_rows);

        if($result->num_rows > 0){
            $hash = password_hash($password, PASSWORD_DEFAULT);
            error_log("Password hash created: " . $hash);
            
            // Update password
            $update_password = $conn->prepare("UPDATE tbl_users SET password = ? WHERE email = ?");
            if(!$update_password){
                error_log("Update prepare failed: " . $conn->error);
                echo "Error - Update prepare failed";
                exit;
            }
            
            $update_password->bind_param("ss", $hash, $email);
            
            if($update_password->execute()){
                error_log("Password updated successfully for: " . $email);
                echo 'updated';
            }
            else{
                error_log("Update execute failed: " . $update_password->error);
                echo 'Error - Update failed';
            }
            $update_password->close();
        }
        else{
            error_log("Email not found in database: " . $email);
            echo "Error - Email not found";
        }
        $check_email->close();
    }
}
else{
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo "Error - Invalid request";
}

$conn->close();
error_log("=== UpdatePassword.php finished ===");
?>