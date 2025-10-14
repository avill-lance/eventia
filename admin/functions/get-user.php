<?php
// Turn off error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any accidental output
ob_start();

session_start();

// CORRECT PATH: Since both files are in admin directory
include __DIR__ . '/../includes/db-config.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    // Clear any buffered output
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$userId = $_GET['user_id'] ?? 0;

if (!$userId) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Clear any buffered output before sending JSON
    ob_end_clean();
    
    if ($user) {
        // Remove password from response for security
        unset($user['password']);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Make sure no extra output is sent
exit();
?>