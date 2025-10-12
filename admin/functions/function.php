<?php



function isAdmin(){
    if(!isset($_SESSION['email']) || !isset($_SESSION['first_name']) || !isset($_SESSION['last_name'])){
        header("Location: dashboard.php");
        exit(0);
    }
}




?>