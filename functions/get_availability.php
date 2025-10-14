<?php
session_start();
require_once __DIR__ . '/database/config.php';

header('Content-Type: application/json');

// Allow CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

$month = $data['month'] ?? null;
$year = $data['year'] ?? null;

if (!$month || !$year) {
    http_response_code(400);
    echo json_encode(['error' => 'Month and year required']);
    exit;
}

try {
    // Get booked dates from database
    $bookedDates = [];
    $stmt = $conn->prepare("
        SELECT DISTINCT event_date 
        FROM tbl_bookings 
        WHERE MONTH(event_date) = ? AND YEAR(event_date) = ?
        AND booking_status IN ('confirmed', 'pending')
    ");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $month, $year);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $bookedDates[] = $row['event_date'];
    }
    
    $stmt->close();
    
    // For now, let's mark some dates as limited for testing
    $limitedDates = [];
    
    // Mark weekends as limited availability
    $startDate = new DateTime("$year-$month-01");
    $endDate = new DateTime("$year-$month-" . $startDate->format('t'));
    
    $currentDate = clone $startDate;
    while ($currentDate <= $endDate) {
        $dateStr = $currentDate->format('Y-m-d');
        $dayOfWeek = $currentDate->format('w'); // 0 = Sunday, 6 = Saturday
        
        // If it's a weekend and not booked, mark as limited
        if (($dayOfWeek == 0 || $dayOfWeek == 6) && !in_array($dateStr, $bookedDates)) {
            $limitedDates[] = $dateStr;
        }
        
        $currentDate->modify('+1 day');
    }
    
    // Also add a few random weekdays as limited for testing
    $testLimitedDates = [
        "$year-$month-20",
        "$year-$month-21", 
        "$year-$month-22"
    ];
    
    foreach ($testLimitedDates as $testDate) {
        if (!in_array($testDate, $bookedDates) && !in_array($testDate, $limitedDates)) {
            $limitedDates[] = $testDate;
        }
    }
    
    echo json_encode([
        'success' => true,
        'booked_dates' => $bookedDates,
        'limited_dates' => $limitedDates,
        'month' => $month,
        'year' => $year,
        'total_booked' => count($bookedDates),
        'total_limited' => count($limitedDates)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Availability check error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>