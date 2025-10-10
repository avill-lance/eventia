<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/database/config.php';

// Debug function
function debug_log($message, $data = null) {
    $log_file = __DIR__ . '/booking_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";
    if ($data !== null) {
        $log_message .= " | Data: " . print_r($data, true);
    }
    $log_message .= "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

$response = array('success' => false, 'message' => '');

try {
    debug_log("Booking request received", [
        'method' => $_SERVER['REQUEST_METHOD'],
        'post_data' => $_POST,
        'files' => $_FILES
    ]);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Check database connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get form data
    $booking_type = $_POST['booking_type'] ?? '';
    $booking_reference = $_POST['booking_reference'] ?? '';
    $package = $_POST['package'] ?? '';
    $venue_type = $_POST['venue_type'] ?? '';
    $venue_id = !empty($_POST['venue_id']) ? intval($_POST['venue_id']) : NULL;
    $venue_address = $_POST['venue_address'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $alternate_contact = $_POST['alternate_contact'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $backup_email = $_POST['backup_email'] ?? '';
    $preferred_contact = $_POST['preferred_contact'] ?? 'Any';
    $special_instructions = $_POST['special_instructions'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $services = $_POST['services'] ?? array();
    
    // Additional fields
    $event_location = $_POST['event_location'] ?? '';
    $full_address = $_POST['full_address'] ?? '';
    $alternate_phone = $_POST['alternate_phone'] ?? '';

    // Validate required fields
    $required_fields = [
        'booking_type' => $booking_type,
        'booking_reference' => $booking_reference,
        'package' => $package,
        'venue_type' => $venue_type,
        'full_name' => $full_name,
        'email' => $email,
        'contact_number' => $contact_number,
        'event_date' => $event_date,
        'event_time' => $event_time,
        'payment_method' => $payment_method
    ];

    $missing_fields = [];
    foreach ($required_fields as $field => $value) {
        if (empty($value)) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missing_fields));
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Validate event date
    $event_date_obj = DateTime::createFromFormat('Y-m-d', $event_date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    if ($event_date_obj < $today) {
        throw new Exception('Event date cannot be in the past');
    }

    // Get package price
    $package_price = 0;
    $price_map = [
        'Wedding Event Package' => 108190,
        'Birthday Celebration Package' => 50000,
        'Corporate Event Package' => 140500,
        'Debut Package' => 95000,
        'Christening Package' => 45000,
        'Anniversary Celebration' => 75000,
        'Holiday Party Package' => 85000,
        'Graduation Party Package' => 35000,
        'Engagement Party Package' => 65000,
        'Reunion Event Package' => 55000
    ];
    
    $package_price = $price_map[$package] ?? 0;
    if ($package_price <= 0) {
        throw new Exception('Invalid package selected: ' . $package);
    }

    // Handle file upload
    $receipt_path = null;
    $payment_methods_requiring_receipt = ['GCash', 'Bank Transfer', 'PayPal', 'Installment'];
    
    if (in_array($payment_method, $payment_methods_requiring_receipt)) {
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/uploads/receipts/';
            
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $file_type = $_FILES['receipt']['type'];
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Invalid file type. Please upload JPEG, PNG, GIF, or PDF files only.');
            }
            
            if ($_FILES['receipt']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File size too large. Maximum size is 5MB.');
            }
            
            $file_extension = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
            $file_name = $booking_reference . '_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_path)) {
                $receipt_path = $target_path;
            } else {
                throw new Exception('Failed to upload receipt file.');
            }
        } else {
            throw new Exception('Receipt upload is required for the selected payment method.');
        }
    }

    // Determine venue location and full address
    if ($venue_type === 'rental' && !empty($venue_id)) {
        $stmt = $conn->prepare("SELECT venue_name, location, description FROM tbl_venues WHERE venue_id = ? AND status = 'available'");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $venue_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $event_location = $row['venue_name'] . ', ' . $row['location'];
            $full_address = $row['description'] ?? $row['location'];
        } else {
            throw new Exception('Selected venue is not available');
        }
        $stmt->close();
    } else if ($venue_type === 'own') {
        if (empty($venue_address)) {
            throw new Exception('Venue address is required for own venue');
        }
        $event_location = $venue_address;
        $full_address = $venue_address;
    }

    // Calculate total amount
    $total_amount = $package_price;

    // FIXED: Correct INSERT statement with proper column count
    $sql = "INSERT INTO tbl_bookings (
        booking_reference, 
        user_id, 
        booking_type, 
        event_type, 
        package_name, 
        package_price,
        venue_type, 
        venue_id, 
        venue_address, 
        event_date, 
        event_time, 
        event_location, 
        full_address,
        contact_name, 
        contact_email, 
        contact_phone, 
        alternate_phone, 
        company_name, 
        backup_email,
        preferred_contact, 
        special_instructions, 
        payment_method, 
        receipt_path, 
        total_amount, 
        booking_status, 
        payment_status
    ) VALUES (?, $_SESSION[id], ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";
    
    debug_log("SQL Query", $sql);
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $event_type = $package;
    
    // Handle NULL values
    $alternate_phone = !empty($alternate_contact) ? $alternate_contact : NULL;
    $company_name = !empty($company_name) ? $company_name : NULL;
    $backup_email = !empty($backup_email) ? $backup_email : NULL;
    $special_instructions = !empty($special_instructions) ? $special_instructions : NULL;
    $venue_address = !empty($venue_address) ? $venue_address : NULL;
    
    // Count the parameters to match the placeholders
    $params = [
        $booking_reference,
        $booking_type,
        $event_type,
        $package,
        $package_price,
        $venue_type,
        $venue_id,
        $venue_address,
        $event_date,
        $event_time,
        $event_location,
        $full_address,
        $full_name,
        $email,
        $contact_number,
        $alternate_phone,
        $company_name,
        $backup_email,
        $preferred_contact,
        $special_instructions,
        $payment_method,
        $receipt_path,
        $total_amount
    ];
    
    debug_log("Parameters count", [
        'placeholders' => substr_count($sql, '?'),
        'parameters' => count($params),
        'parameters_list' => $params
    ]);
    
    // FIXED: Correct bind_param types - count should match placeholders (25)
    $bind_result = $stmt->bind_param("ssssdsisssssssssssssssd",
        $params[0],  // booking_reference
        $params[1],  // booking_type
        $params[2],  // event_type
        $params[3],  // package_name
        $params[4],  // package_price
        $params[5],  // venue_type
        $params[6],  // venue_id
        $params[7],  // venue_address
        $params[8],  // event_date
        $params[9],  // event_time
        $params[10], // event_location
        $params[11], // full_address
        $params[12], // contact_name
        $params[13], // contact_email
        $params[14], // contact_phone
        $params[15], // alternate_phone
        $params[16], // company_name
        $params[17], // backup_email
        $params[18], // preferred_contact
        $params[19], // special_instructions
        $params[20], // payment_method
        $params[21], // receipt_path
        $params[22]  // total_amount
    );
    
    if (!$bind_result) {
        throw new Exception('Bind failed: ' . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $booking_id = $stmt->insert_id;
    $stmt->close();
    
    debug_log("Booking inserted successfully", ['booking_id' => $booking_id]);

    // Insert selected services
    if (!empty($services) && is_array($services)) {
        $service_stmt = $conn->prepare("INSERT INTO tbl_booking_services (booking_id, service_name, service_description, service_price) VALUES (?, ?, ?, ?)");
        if (!$service_stmt) {
            throw new Exception('Service prepare failed: ' . $conn->error);
        }
        
        $service_prices = [
            'Catering Service' => 15000,
            'Decoration Setup' => 10000,
            'Photography & Videography' => 12000,
            'Sound System & Lights' => 8000,
            'Entertainment' => 10000,
            'Event Coordination' => 15000,
            'Invitation Design & Printing' => 5000,
            'Souvenirs & Giveaways' => 3000
        ];
        
        foreach ($services as $service) {
            $service_price = $service_prices[$service] ?? 0;
            $service_description = "Additional service: " . $service;
            
            $service_stmt->bind_param("issd", $booking_id, $service, $service_description, $service_price);
            if (!$service_stmt->execute()) {
                throw new Exception('Failed to save service: ' . $service_stmt->error);
            }
            
            $total_amount += $service_price;
        }
        
        $service_stmt->close();
        
        // Update total amount
        $update_stmt = $conn->prepare("UPDATE tbl_bookings SET total_amount = ? WHERE booking_id = ?");
        if (!$update_stmt) {
            throw new Exception('Update prepare failed: ' . $conn->error);
        }
        
        $update_stmt->bind_param("di", $total_amount, $booking_id);
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update total amount: ' . $update_stmt->error);
        }
        $update_stmt->close();
        
        debug_log("Services processed", [
            'services_count' => count($services),
            'new_total_amount' => $total_amount
        ]);
    }

    $response['success'] = true;
    $response['message'] = 'Booking submitted successfully';
    $response['booking_reference'] = $booking_reference;
    $response['total_amount'] = $total_amount;
    $response['booking_id'] = $booking_id;

    debug_log("Booking completed successfully", $response);

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    debug_log("Booking failed", $response);
}

echo json_encode($response);
?>