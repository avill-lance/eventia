<?php
require_once 'database/config.php';

// Test current month
$month = date('n');
$year = date('Y');

$stmt = $conn->prepare("
    SELECT DISTINCT event_date, COUNT(*) as booking_count 
    FROM tbl_bookings 
    WHERE MONTH(event_date) = ? AND YEAR(event_date) = ?
    AND booking_status IN ('confirmed', 'pending')
    GROUP BY event_date
");
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Booked Dates for $month/$year</h2>";
while ($row = $result->fetch_assoc()) {
    echo "Date: " . $row['event_date'] . " - Bookings: " . $row['booking_count'] . "<br>";
}

// Show all dates that should be available
echo "<h2>All dates should be available unless listed above</h2>";
exit();
// session_start();
// require_once __DIR__ . '/database/config.php';

// header('Content-Type: application/json');

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     echo json_encode(['error' => 'Invalid request method']);
//     exit;
// }

// $data = json_decode(file_get_contents('php://input'), true);
// $month = $data['month'] ?? null;
// $year = $data['year'] ?? null;

// if (!$month || !$year) {
//     echo json_encode(['error' => 'Month and year required']);
//     exit;
// }

// try {
//     // Get booked dates from database - checking both confirmed and pending bookings
//     $bookedDates = [];
//     $stmt = $conn->prepare("
//         SELECT DISTINCT event_date, COUNT(*) as booking_count 
//         FROM tbl_bookings 
//         WHERE MONTH(event_date) = ? AND YEAR(event_date) = ?
//         AND booking_status IN ('confirmed', 'pending')
//         GROUP BY event_date
//     ");
//     $stmt->bind_param("ii", $month, $year);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     while ($row = $result->fetch_assoc()) {
//         $bookedDates[$row['event_date']] = $row['booking_count'];
//     }
    
//     // Get venue capacity information
//     $venues = [];
//     $venueStmt = $conn->prepare("SELECT venue_id, venue_name, capacity FROM tbl_venues WHERE status = 'available'");
//     $venueStmt->execute();
//     $venueResult = $venueStmt->get_result();
    
//     while ($venue = $venueResult->fetch_assoc()) {
//         $venues[$venue['venue_id']] = $venue;
//     }
    
//     // Calculate limited availability dates
//     // A date is considered "limited" if it has some bookings but not at full capacity
//     $limitedDates = [];
    
//     // For simplicity, we'll consider a date limited if it has more than 2 bookings
//     // You can adjust this logic based on your business rules
//     foreach ($bookedDates as $date => $bookingCount) {
//         if ($bookingCount >= 2 && $bookingCount < 5) { // Adjust these numbers as needed
//             $limitedDates[] = $date;
//         }
//     }
    
//     // Also mark weekends as limited availability
//     $startDate = new DateTime("$year-$month-01");
//     $endDate = new DateTime("$year-$month-" . $startDate->format('t'));
    
//     while ($startDate <= $endDate) {
//         $dateStr = $startDate->format('Y-m-d');
//         $dayOfWeek = $startDate->format('w'); // 0 = Sunday, 6 = Saturday
        
//         // If it's a weekend and not already booked, mark as limited
//         if (($dayOfWeek == 0 || $dayOfWeek == 6) && !isset($bookedDates[$dateStr]) && !in_array($dateStr, $limitedDates)) {
//             $limitedDates[] = $dateStr;
//         }
        
//         $startDate->modify('+1 day');
//     }
    
//     // Convert bookedDates array to simple array of dates for the frontend
//     $bookedDatesSimple = array_keys($bookedDates);
    
//     echo json_encode([
//         'booked_dates' => $bookedDatesSimple,
//         'limited_dates' => $limitedDates,
//         'month' => $month,
//         'year' => $year,
//         'total_booked_dates' => count($bookedDatesSimple),
//         'total_limited_dates' => count($limitedDates)
//     ]);
    
// } catch (Exception $e) {
//     error_log('Availability check error: ' . $e->getMessage());
//     echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
// }
?>