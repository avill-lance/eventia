<?php

    // ### Establish Database Connection ###
    include  __DIR__ ."/../database/config.php";

    // ### Establish Session ###
    include  __DIR__ . "/session.php";

    if($_SERVER["REQUEST_METHOD"]=="POST"){
        // $rememberMe = isset($_POST['rememberMe']) ? htmlspecialchars(string: $_POST['rememberMe']) : '';
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
                    $_SESSION['email']=$row['email']; 
                    echo"inactive";
                }
                else{
                    if(password_verify($password, $row['password'])){
                        // if(isset($rememberMe)){
                        //     rememberMe($email);
                        // }
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

function rememberMe($email){
    // Set session to expire in 30 days
    $_SESSION['rememberMe'] = [
        'email' => $email,  
        'created_at' => time(),
        'expires_at' => time() + (30 * 24 * 60 * 60),
        'expires_at_readable' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60))
    ];
    
    // Also configure PHP session to last 30 days
    ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
    session_set_cookie_params(30 * 24 * 60 * 60);
    
    // Set remember me cookie for 30 days
    setcookie('rememberMe', 'true', time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }
?>