<?php

// ### Establish Database Connection ###
include   __DIR__ . "/../database/config.php";

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
                $insert_user = $conn->prepare("INSERT INTO tbl_users (first_name,	last_name,	email,	phone,	city,	zip,	address,	password	) VALUES(?,?,?,?,?,?,?,?);");
                $insert_user->bind_param("ssssssss", $first_name,$last_name,$email,$phone,$city,$zip,$address,$hash);
                
                if($insert_user->execute()){
                        echo "added";
                }
                else{
                    echo "error";
                }
            }
        }
    }
    //This is an added comment
    //xdxdxd
}
?>