<?php
// ### Creates connection between PHP and MYSQL ###
$servername = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'eventia_users';
$conn = new mysqli($servername,$user,$pass,$db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>