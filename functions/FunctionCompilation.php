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
    if(!isset($_SESSION['token'])){
    header("Location: login.php");
    exit(0);
    }
}


//Checks if an id session is set
if(isset($_SESSION["id"])){
    $query = "SELECT * FROM tbl_users WHERE user_id = '$_SESSION[id]'";;
    $result = $conn->query($query);

    if($result){
        $rows = $result->fetch_assoc();
    }
}

if(isset($_POST["passBtn"])){
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $zip = $_POST["zip"];
    $city = $_POST["city"];
    $address = $_POST["address"];

    //Check if email already exists in another account
    $query = "SELECT * FROM tbl_users WHERE email = '$email' and user_id != '$_SESSION[id]'";
    $result = $conn->query($query);

    if($result->num_rows > 0){
        echo "Email already exists.";
    }else{
        $query = "UPDATE tbl_users SET first_name = '$firstname', last_name = '$lastname', email = '$email', phone = '$phone', zip = '$zip', city = '$city', address = '$address' WHERE user_id = '$_SESSION[id]'";
        $result = $conn->query($query);

    if($result){
        $_SESSION["first_name"] = $firstname;
        $_SESSION["last_name"] = $lastname;
        $_SESSION["email"] = $email;
        echo "success";
        header("Location: profile.php");
    }
    }
}


?>