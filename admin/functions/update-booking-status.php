<?php
include __DIR__ . '/../includes/db-config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $booking_id = $_POST['booking_id'] ?? null;
    $booking_status = $_POST['booking_status'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (!$booking_id || !in_array($booking_status, ['pending', 'confirmed', 'cancelled', 'completed']) || !in_array($payment_status, ['pending', 'partial', 'paid', 'cancelled'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE tbl_bookings SET booking_status = ?, payment_status = ?, updated_at = NOW() WHERE booking_id = ?");
    $stmt->bind_param("ssi", $booking_status, $payment_status, $booking_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>