<?php
// ### Establish Database Connection ###
include  __DIR__ ."/../functions/session.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header first
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Your PayMongo TEST Secret Key
    $secretKey = "sk_test_rj6TvFQRA4PKi88QGLraUWDv";

    // Collect ALL form data for booking
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) * 100 : 99999;
    $_SESSION['amount'] = $amount;
    
    // Store ALL booking data in session for later use
    $_SESSION['pending_booking'] = [
        'amount' => $amount / 100,
        'firstName' => $_POST['firstName'] ?? '',
        'lastName' => $_POST['lastName'] ?? '',
        'email' => $_POST['email'] ?? '',
        'eventType' => $_POST['eventType'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'package' => $_POST['package'] ?? '',
        'venue_type' => $_POST['venue_type'] ?? '',
        'venue_id' => $_POST['venue_id'] ?? '',
        'event_location' => $_POST['event_location'] ?? '',
        'full_address' => $_POST['full_address'] ?? '',
        'event_date' => $_POST['event_date'] ?? '',
        'event_time' => $_POST['event_time'] ?? '',
        'guest_count' => $_POST['guest_count'] ?? '',
        'special_instructions' => $_POST['special_instructions'] ?? '',
        'services' => $_POST['services'] ?? [],
        'customizations' => $_POST['customization'] ?? [],
        'booking_reference' => $_POST['booking_reference'] ?? '',
        'booking_type' => 'self'
    ];

    // Generate a unique reference first
    $referenceNumber = $_POST['booking_reference'] ?? 'EVT-' . date('Ymd-His') . '-' . uniqid();

    // Use Checkout Sessions API
    $sessionData = [
        "data" => [
            "attributes" => [
                "line_items" => [
                    [
                        "amount" => $amount,
                        "currency" => "PHP",
                        "name" => ($_SESSION['pending_booking']['eventType'] ?? 'Event') . " - " . ($_SESSION['pending_booking']['firstName'] ?? '') . " " . ($_SESSION['pending_booking']['lastName'] ?? ''),
                        "quantity" => 1
                    ]
                ],
                "payment_method_types" => ["card", "gcash", "grab_pay"],
                "success_url" => "http://" . $_SERVER['HTTP_HOST'] . "/eventia/payment-status.php?success=true&ref=" . $referenceNumber,
                "cancel_url" => "http://" . $_SERVER['HTTP_HOST'] . "/eventia/payment-status.php?success=false&ref=" . $referenceNumber,
                "description" => ($_SESSION['pending_booking']['eventType'] ?? 'Event') . " Booking",
                "send_email_receipt" => false,
                "reference_number" => $referenceNumber
            ]
        ]
    ];

    // Convert to JSON
    $jsonData = json_encode($sessionData);
    
    if ($jsonData === false) {
        throw new Exception('JSON encode failed: ' . json_last_error_msg());
    }

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paymongo.com/v1/checkout_sessions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode($secretKey . ":")
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    // Execute request
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        throw new Exception('cURL error: ' . $curlError);
    }

    // Decode response
    $response = json_decode($result, true);

    if ($httpCode == 200 && isset($response['data']['attributes']['checkout_url'])) {
        $checkoutUrl = $response['data']['attributes']['checkout_url'];
        $sessionId = $response['data']['id'] ?? 'unknown';
        
        echo json_encode([
            'success' => true,
            'checkout_url' => $checkoutUrl,
            'reference' => $referenceNumber,
            'session_id' => $sessionId,
            'message' => 'You will be redirected to PayMongo.'
        ]);

    } else {
        // Error response
        $errorDetail = 'Unknown error';
        if(isset($response['errors'][0]['detail'])) {
            $errorDetail = $response['errors'][0]['detail'];
        } elseif($curlError) {
            $errorDetail = $curlError;
        }
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $errorDetail,
            'http_code' => $httpCode
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>