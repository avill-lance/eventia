<?php
// functions/ViewTransactions.php
session_start();

// Set JSON header FIRST
header('Content-Type: application/json; charset=utf-8');

// Prevent any output
ob_clean();

// Include database configuration
require_once __DIR__ . "/../database/config.php";

// Initialize response
$response = [
    'success' => false,
    'message' => 'Initializing...',
    'data' => [],
    'count' => 0
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        exit;
    }

    // Check database connection
    if (!$conn) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit;
    }

    $user_id = $_SESSION['id'];
    
    // Get transactions for this user
    $stmt = $conn->prepare("SELECT transaction_id, ref_id, date_time, status, price FROM tbl_transactions WHERE user_id = ? ORDER BY date_time DESC");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $transactions[] = [
                'transaction_id' => $row['transaction_id'] ?? 'N/A',
                'ref_id' => $row['ref_id'] ?? 'N/A',
                'date_time' => $row['date_time'] ?? '',
                'status' => $row['status'] ?? 'pending',
                'price' => $row['price'] ?? '0.00'
            ];
        }
    }
    
    $response['success'] = true;
    $response['data'] = $transactions;
    $response['count'] = count($transactions);
    $response['message'] = 'Transactions loaded successfully';
    
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("ViewTransactions Error: " . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
exit;
?>