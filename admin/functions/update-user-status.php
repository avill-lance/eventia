<?php
session_start();
include __DIR__ . '/../includes/db-config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Verify CSRF token
if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    $userId = $input['user_id'] ?? '';
    $status = $input['status'] ?? '';
    
    if (empty($userId) || empty($status)) {
        $response['message'] = 'User ID and status are required';
        echo json_encode($response);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE tbl_users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $status, $userId);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User status updated successfully';
    } else {
        $response['message'] = 'Error updating user status: ' . $stmt->error;
    }
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>