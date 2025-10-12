<?php

if($_SERVER['REQUEST_METHOD']=='POST'){
    
    // Check if this is a test payment
    // $isTest = isset($_POST['test_mode']) && $_POST['test_mode'] === 'true';
    
    // if($isTest) {
    //     // Simulate payment for local testing
    //     $referenceNumber = 'TEST-' . uniqid();
    //     $localUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/../payment-status.php?ref=" . $referenceNumber . "&test=true";
        
    //     // Log the test payment
    //     error_log("=== TEST PAYMENT ===");
    //     error_log("Reference: " . $referenceNumber);
    //     error_log("Redirect URL: " . $localUrl);
    //     error_log("=== END TEST PAYMENT ===");
        
    //     header('Content-Type: application/json');
    //     echo json_encode([
    //         'success' => true,
    //         'checkout_url' => $localUrl,
    //         'reference' => $referenceNumber,
    //         'test_mode' => true
    //     ]);
    //     exit;
    // }
    
    // Your PayMongo TEST Secret Key
    $secretKey = "sk_test_rj6TvFQRA4PKi88QGLraUWDv";

    // Collect form data
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) * 100 : 99999; // Convert to centavo
    $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : 'Test';
    $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : 'User';
    $email = isset($_POST['email']) ? $_POST['email'] : 'test@example.com';
    $eventType = isset($_POST['eventType']) ? $_POST['eventType'] : 'Event';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';

    // Generate a unique reference first
    $referenceNumber = 'EVT-' . date('Ymd-His') . '-' . uniqid();

    // Use Checkout Sessions API instead (better for redirects)
    $sessionData = [
        "data" => [
            "attributes" => [
                "line_items" => [
                    [
                        "amount" => $amount,
                        "currency" => "PHP",
                        "name" => $eventType . " - " . $firstName . " " . $lastName,
                        "quantity" => 1
                    ]
                ],
                "payment_method_types" => ["card", "gcash", "grab_pay"],
                "success_url" => "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/../payment-status.php?success=true&ref=" . $referenceNumber,
                "cancel_url" => "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/../payment-status.php?success=false&ref=" . $referenceNumber,
                "description" => $eventType . " Booking",
                "send_email_receipt" => false,
                "reference_number" => $referenceNumber
            ]
        ]
    ];

    // Convert to JSON
    $jsonData = json_encode($sessionData);
    
    // Log the request
    error_log("=== PayMongo Checkout Session Request ===");
    error_log("URL: https://api.paymongo.com/v1/checkout_sessions");
    error_log("Data: " . $jsonData);

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

    // Log response
    error_log("HTTP Code: " . $httpCode);
    error_log("Response: " . $result);
    if($curlError) {
        error_log("cURL Error: " . $curlError);
    }
    error_log("=== End Request ===");

    // Write to file for debugging
    file_put_contents(__DIR__ . '/payment_response.txt', "HTTP Code: " . $httpCode . "\nResponse: " . print_r(json_decode($result, true), true));

    // Decode response
    $response = json_decode($result, true);

    // Check for success
    if ($httpCode == 200 && isset($response['data']['attributes']['checkout_url'])) {
        $checkoutUrl = $response['data']['attributes']['checkout_url'];
        $sessionId = $response['data']['id'] ?? 'unknown';
        
        // Store payment session in file for status checking
        $paymentData = [
            'session_id' => $sessionId,
            'reference_number' => $referenceNumber,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'customer' => $firstName . ' ' . $lastName,
            'email' => $email,
            'amount' => $amount / 100
        ];
        
        file_put_contents(__DIR__ . '/payment_sessions.json', json_encode($paymentData) . "\n", FILE_APPEND);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'checkout_url' => $checkoutUrl,
            'reference' => $referenceNumber,
            'session_id' => $sessionId,
            'message' => 'You will be redirected to PayMongo. After payment, please return to this site manually.'
        ]);

    } else {
        // Error response
        header('Content-Type: application/json');
        http_response_code(400);
        
        $errorDetail = 'Unknown error';
        if(isset($response['errors'][0]['detail'])) {
            $errorDetail = $response['errors'][0]['detail'];
        } elseif($curlError) {
            $errorDetail = $curlError;
        }
        
        echo json_encode([
            'success' => false,
            'error' => $errorDetail,
            'http_code' => $httpCode
        ]);
    }
} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
}