<?php
header('Content-Type: application/json');
require_once __DIR__ . "/database/config.php";

try {
    $sql = "SELECT venue_id, venue_name, venue_type, capacity, location, price, description, image_url, amenities, status 
            FROM tbl_venues 
            WHERE status = 'available' 
            ORDER BY venue_name ASC";

    $result = $conn->query($sql);

    $venues = array();

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Ensure numeric values are properly formatted
            $row['capacity'] = (int)$row['capacity'];
            $row['price'] = floatval($row['price']);
            $venues[] = $row;
        }
    }

    echo json_encode($venues);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch venues: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>