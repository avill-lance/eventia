<?php
// includes/db-config.php
// Database configuration for Eventia Admin

$servername = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'eventia_users';

try {
    $conn = new mysqli($servername, $user, $pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for proper encoding
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>