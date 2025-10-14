<?php
include __DIR__ . '/../database/db-config.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT * FROM tbl_venues WHERE status = 'active' ORDER BY venue_name");
    $stmt->execute();
    $result = $stmt->get_result();
    $venues = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode($venues);
} catch (Exception $e) {
    echo json_encode([]);
}

$conn->close();
?>