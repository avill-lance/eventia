<?php
session_start();
require_once __DIR__ . '/../database/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Support both POST and GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
} else {
    $data = $_GET;
}

$month = $data['month'] ?? date('n');
$year = $data['year'] ?? date('Y');

try {
    // Get all dates in the month
    $startDate = new DateTime("$year-$month-01");
    $endDate = new DateTime("$year-$month-" . $startDate->format('t'));
    
    $bookedDates = [];
    $limitedDates = [];
    
    // Check each date in the month for bookings
    $currentDate = clone $startDate;
    while ($currentDate <= $endDate) {
        $dateStr = $currentDate->format('Y-m-d');
        $dayOfWeek = $currentDate->format('w'); // 0 = Sunday, 6 = Saturday
        
        // FIXED: Include bookings with empty status (treat as confirmed)
        $stmt = $conn->prepare("
            SELECT COUNT(*) as booking_count 
            FROM tbl_bookings 
            WHERE event_date = ? 
            AND (booking_status IN ('confirmed', 'pending') OR booking_status = '' OR booking_status IS NULL)
            AND (contact_name != '' OR contact_email != '')  -- At least some contact info
        ");
        $stmt->bind_param("s", $dateStr);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $bookingCount = $row['booking_count'] ?? 0;
        
        // Determine availability based on booking count
        if ($bookingCount >= 2) { // Fully booked if 2 or more bookings
            $bookedDates[] = $dateStr;
          //  echo "🔴 $dateStr: $bookingCount bookings - FULLY BOOKED\n";
        } else if ($bookingCount >= 1) {
            $limitedDates[] = $dateStr;
           // echo "🟡 $dateStr: $bookingCount bookings - LIMITED\n";
        } else {
           // echo "🟢 $dateStr: $bookingCount bookings - AVAILABLE\n";
        }
        
        $stmt->close();
        $currentDate->modify('+1 day');
    }
    
    echo json_encode([
        'success' => true,
        'booked_dates' => $bookedDates,
        'limited_dates' => $limitedDates,
        'month' => $month,
        'year' => $year,
        'total_booked' => count($bookedDates),
        'total_limited' => count($limitedDates),
        'debug_info' => "Found " . count($bookedDates) . " booked dates and " . count($limitedDates) . " limited dates"
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Availability check error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'success' => false
    ]);
}
?>