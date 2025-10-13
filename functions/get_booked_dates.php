<?php
// get_booked_dates.php
session_start();
require_once __DIR__ . '/database/config.php';

header('Content-Type: application/json');

$bookedDates = [];

try {
    // Query to get all booked dates from confirmed and pending bookings
    $query = "SELECT DISTINCT event_date FROM tbl_bookings WHERE status IN ('confirmed', 'pending')";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Format date as YYYY-MM-DD
            $bookedDates[] = $row['event_date'];
        }
        
        echo json_encode([
            'success' => true,
            'bookedDates' => $bookedDates
        ]);
    } else {
        throw new Exception("Failed to execute query: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error in get_booked_dates.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'bookedDates' => []
    ]);
}

$conn->close();
?>