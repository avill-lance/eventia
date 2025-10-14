<?php
include __DIR__ . '/../includes/db-config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$booking_id = $data['booking_id'] ?? null;

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

try {
    // In a real implementation, you would send an actual email here
    // This is just a placeholder for the email functionality
    
    echo json_encode(['success' => true, 'message' => 'Reminder sent successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>