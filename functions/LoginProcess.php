<?php

    // ### Establish Database Connection ###
    include  __DIR__ ."/../database/config.php";

    // ### Establish Session ###
    include  __DIR__ . "/session.php";

    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $email = htmlspecialchars($_POST['email'])??'';
        $password = htmlspecialchars($_POST['password'])??'';

        if(empty($email) || empty($password)){
            echo"empty";
        }
        else{
            $check_email= $conn->prepare("SELECT * FROM tbl_users where email=? LIMIT 1");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $result = $check_email->get_result();

            if($result->num_rows>0){
                $row = $result->fetch_assoc();

                if($row['status']==="inactive"){
                    echo"inactive";
                }
                else{
                    if(password_verify($password, $row['password'])){
                    $_SESSION["id"] = $row['user_id'];
                    $_SESSION['email']=$row['email'];
                    $_SESSION['first_name']=$row['first_name'];
                    $_SESSION['last_name']=$row['last_name'];
                    echo "success";
                    }
                    else{
                        echo "wrong";
                    }
                    }
            }
            else{
                echo "invalid";
            }
        }
    }
?>