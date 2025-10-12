<?php
require_once __DIR__ . '/database/config.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT venue_id, venue_name, venue_type, capacity, location, price, description, amenities FROM tbl_venues WHERE status = 'available'";
    $result = $conn->query($sql);
    
    $venues = [];
    while ($row = $result->fetch_assoc()) {
        $venues[] = $row;
    }
    
    echo json_encode($venues);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>