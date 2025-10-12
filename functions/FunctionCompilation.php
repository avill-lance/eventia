<?php 

//Establish database connection
include  __DIR__ . "/../database/config.php";

//Checks if there is an active session and log out if none.
function isLoggedIn(){
    if(!isset($_SESSION['email']) || !isset($_SESSION['first_name']) || !isset($_SESSION['last_name'])){
        header("Location: login.php");
        exit(0);
    }
}

//Checks if there is an active session for user to change their password.
function checkToken(){
    if(!isset($_SESSION['pending_verification'])){
        header("Location: login.php");
        exit(0);
    }
}

// Secure function to get user data with proper error handling
function getUserData($user_id, $conn) {
    if(!$conn) {
        error_log("Database connection not available in getUserData");
        return null;
    }
    
    try {
        $query = "SELECT * FROM tbl_users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        
        if(!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        $stmt->close();
        return null;
        
    } catch (Exception $e) {
        error_log("Database error in getUserData: " . $e->getMessage());
        return null;
    }
}

// Only run user query if specifically needed, not automatically on include
if(isset($_SESSION["id"]) && !isset($rows)) {
    $rows = getUserData($_SESSION["id"], $conn);
}

// Profile update handling - only execute when form is submitted
if(isset($_POST["passBtn"])){
    $firstname = htmlspecialchars($_POST["firstname"] ?? '');
    $lastname = htmlspecialchars($_POST["lastname"] ?? '');
    $email = htmlspecialchars($_POST["email"] ?? '');
    $phone = htmlspecialchars($_POST["phone"] ?? '');
    $zip = htmlspecialchars($_POST["zip"] ?? '');
    $city = htmlspecialchars($_POST["city"] ?? '');
    $address = htmlspecialchars($_POST["address"] ?? '');

    if(!$conn) {
        echo "Database connection error.";
        exit;
    }

    try {
        // Check if email already exists in another account
        $checkQuery = "SELECT * FROM tbl_users WHERE email = ? AND user_id != ?";
        $checkStmt = $conn->prepare($checkQuery);
        
        if(!$checkStmt) {
            echo "Database prepare error.";
            exit;
        }
        
        $checkStmt->bind_param("si", $email, $_SESSION["id"]);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if($result->num_rows > 0){
            echo "Email already exists.";
        } else {
            // Update user data
            $updateQuery = "UPDATE tbl_users SET first_name = ?, last_name = ?, email = ?, phone = ?, zip = ?, city = ?, address = ? WHERE user_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            
            if(!$updateStmt) {
                echo "Database prepare error.";
                exit;
            }
            
            $updateStmt->bind_param("sssssssi", $firstname, $lastname, $email, $phone, $zip, $city, $address, $_SESSION["id"]);
            
            if($updateStmt->execute()){
                $_SESSION["first_name"] = $firstname;
                $_SESSION["last_name"] = $lastname;
                $_SESSION["email"] = $email;
                echo "success";
                // Remove header redirect as it interferes with AJAX response
            } else {
                echo "Update failed: " . $conn->error;
            }
            
            $updateStmt->close();
        }
        
        $checkStmt->close();
        
    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        echo "An error occurred while updating profile.";
    }
}

?>