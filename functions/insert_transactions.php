<?php
include __DIR__.'/session.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Debug session
$debug_info = [
    'session_id' => session_id(),
    'id_set' => isset($_SESSION['id']),
    'id_value' => $_SESSION['id'] ?? 'NOT_SET'
];

// ### Establish Database Connection ###
include __DIR__ . "/../database/config.php";

// Check database connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'debug' => $debug_info
    ]);
    exit;
}

// Check if user is logged in
if(!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'debug' => $debug_info
    ]);
    exit;
}

try {
    $refernce_id=$_SESSION[''];
    $user_id = $_SESSION['id'];
    
    // Get transactions for this user - use user_id
    $stmt = $conn->prepare("SELECT transaction_id, ref_id, date_time, status, price FROM tbl_transactions WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }
    
    // Debug: Check what columns we actually have
    $actual_columns = [];
    if(!empty($transactions)) {
        $actual_columns = array_keys($transactions[0]);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $transactions,
        'count' => count($transactions),
        'debug' => [
            'user_id_used' => $user_id,
            'rows_found' => $result->num_rows,
            'actual_columns' => $actual_columns,
            'session_info' => $debug_info
        ]
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage(),
        'debug' => $debug_info
    ]);
}

$conn->close();
?>