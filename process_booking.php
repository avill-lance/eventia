<?php
session_start();
require_once __DIR__ . '/database/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get form data
    $booking_reference = $_POST['booking_reference'] ?? '';
    $user_id = $_SESSION['id'] ?? null; // Assuming user is logged in
    $booking_type = $_POST['booking_type'] ?? 'self';
    $event_type = $_POST['package'] ?? '';
    $package_name = $_POST['package'] ?? '';
    $venue_type = $_POST['venue_type'] ?? '';
    $venue_id = $_POST['venue_id'] ?? null;
    $venue_address = $_POST['venue_address'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $event_location = $_POST['event_location'] ?? '';
    $full_address = $_POST['full_address'] ?? '';
    
    // Customer information
    $contact_name = $_POST['full_name'] ?? '';
    $contact_email = $_POST['email'] ?? '';
    $contact_phone = $_POST['contact_number'] ?? '';
    $alternate_phone = $_POST['alternate_phone'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $backup_email = $_POST['backup_email'] ?? '';
    $preferred_contact = $_POST['preferred_contact'] ?? 'Any';
    
    $guest_count = $_POST['guest_count'] ?? 0;
    $special_instructions = $_POST['special_instructions'] ?? '';
    
    // Payment
    $payment_method = $_POST['payment_method'] ?? '';
    $payment_status = 'pending';
    
    // Calculate total amount (you might want to calculate this properly)
    $total_amount = 0;
    
    // Get package price
    $package_stmt = $conn->prepare("SELECT base_price FROM tbl_packages WHERE package_name = ?");
    $package_stmt->bind_param("s", $package_name);
    $package_stmt->execute();
    $package_result = $package_stmt->get_result();
    if ($package_row = $package_result->fetch_assoc()) {
        $total_amount += $package_row['base_price'];
    }
    $package_stmt->close();

    // Insert booking
    $booking_sql = "INSERT INTO tbl_bookings (
        booking_reference, user_id, booking_type, event_type, package_name, 
        venue_type, venue_id, venue_address, event_date, event_time, 
        event_location, full_address, contact_name, contact_email, contact_phone, 
        alternate_phone, company_name, backup_email, preferred_contact, 
        guest_count, special_instructions, payment_method, payment_status, 
        booking_status, total_amount
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_status = 'pending';
    $booking_stmt->bind_param(
        "sissssissssssssssssisssds", 
        $booking_reference, $user_id, $booking_type, $event_type, $package_name,
        $venue_type, $venue_id, $venue_address, $event_date, $event_time,
        $event_location, $full_address, $contact_name, $contact_email, $contact_phone,
        $alternate_phone, $company_name, $backup_email, $preferred_contact,
        $guest_count, $special_instructions, $payment_method, $payment_status,
        $booking_status, $total_amount
    );
    
    if (!$booking_stmt->execute()) {
        throw new Exception("Failed to create booking: " . $booking_stmt->error);
    }
    
    $booking_id = $conn->insert_id;
    
    // Handle services
    if (isset($_POST['services']) && is_array($_POST['services'])) {
        foreach ($_POST['services'] as $service_name) {
            // Get service details
            $service_stmt = $conn->prepare("SELECT service_id, service_name, service_description, base_price FROM tbl_services WHERE service_name = ?");
            $service_stmt->bind_param("s", $service_name);
            $service_stmt->execute();
            $service_result = $service_stmt->get_result();
            
            if ($service_row = $service_result->fetch_assoc()) {
                $service_sql = "INSERT INTO tbl_booking_services (booking_id, service_id, service_name, service_description, base_price, final_price) VALUES (?, ?, ?, ?, ?, ?)";
                $service_insert_stmt = $conn->prepare($service_sql);
                $service_insert_stmt->bind_param(
                    "iissdd",
                    $booking_id,
                    $service_row['service_id'],
                    $service_row['service_name'],
                    $service_row['service_description'],
                    $service_row['base_price'],
                    $service_row['base_price']
                );
                
                if (!$service_insert_stmt->execute()) {
                    throw new Exception("Failed to add service: " . $service_insert_stmt->error);
                }
                
                $service_insert_stmt->close();
                $total_amount += $service_row['base_price'];
            }
            $service_stmt->close();
        }
    }
    
    // Update total amount
    $update_sql = "UPDATE tbl_bookings SET total_amount = ? WHERE booking_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("di", $total_amount, $booking_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'booking_reference' => $booking_reference,
        'booking_id' => $booking_id,
        'total_amount' => $total_amount
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>