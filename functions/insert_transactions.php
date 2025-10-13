<?php
include __DIR__.'/session.php';
include __DIR__ . "/../database/config.php";

$success = isset($_GET['success']) && $_GET['success'] === 'true';
$ref = $_GET['ref'] ?? '';
$user_id =$_SESSION['id'];
$price =$_SESSION['amount'];
if ($success) {
    $status='PAID';
    $title = "Payment Successful!";
    $message = "Thank you for your payment. Your booking has been confirmed.";
    $alertClass = "alert-success";
    $icon = "bi-check-circle";
} else {
    $status='CANCELLED';
    $title = "Payment Cancelled";
    $message = "Your payment was cancelled. You can try again anytime.";
    $alertClass = "alert-warning";
    $icon = "bi-x-circle";
}

$insertsql= $conn->prepare("INSERT INTO tbl_transactions (user_id,ref_id,status,price) VALUES (?,?,?,?)");
$insertsql->bind_param("issi", $user_id,$ref,$status,$price);
if($insertsql->execute()){
    if( isset($_SESSION['amount'])){
        unset($_SESSION['amount']);
    }
}
$insertsql->close();
?>