<?php
include __DIR__ . '/../includes/db-config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get all form data
    $booking_id = $_POST['booking_id'] ?? null;
    $booking_reference = $_POST['booking_reference'] ?? '';
    $booking_type = $_POST['booking_type'] ?? 'self';
    $event_type = $_POST['event_type'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $event_location = $_POST['event_location'] ?? '';
    $guest_count = $_POST['guest_count'] ?? null;
    $venue_type = $_POST['venue_type'] ?? 'own';
    $venue_id = !empty($_POST['venue_id']) ? $_POST['venue_id'] : null;
    $package_id = !empty($_POST['package_id']) ? $_POST['package_id'] : null;
    $contact_name = $_POST['contact_name'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    $contact_phone = $_POST['contact_phone'] ?? '';
    $alternate_phone = $_POST['alternate_phone'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $preferred_contact = $_POST['preferred_contact'] ?? 'Any';
    $special_instructions = $_POST['special_instructions'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'GCash';
    $payment_status = $_POST['payment_status'] ?? 'pending';
    $booking_status = $_POST['booking_status'] ?? 'pending';
    $total_amount = $_POST['total_amount'] ?? 0;

    // Validate required fields
    if (empty($booking_reference) || empty($event_type) || empty($event_date) || empty($contact_name) || empty($contact_email) || empty($contact_phone)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }

    // Validate foreign key constraints
    if ($venue_id && !isValidVenue($conn, $venue_id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid venue selected']);
        exit;
    }

    if ($package_id && !isValidPackage($conn, $package_id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid package selected']);
        exit;
    }

    if ($booking_id) {
        // Update existing booking
        $stmt = $conn->prepare("UPDATE tbl_bookings SET 
            booking_type=?, event_type=?, event_date=?, event_time=?, event_location=?,
            guest_count=?, venue_type=?, venue_id=?, package_id=?, contact_name=?,
            contact_email=?, contact_phone=?, alternate_phone=?, company_name=?,
            preferred_contact=?, special_instructions=?, payment_method=?,
            payment_status=?, booking_status=?, total_amount=?, updated_at=NOW()
            WHERE booking_id=?"
        );
        $stmt->bind_param("sssssisissssssssssdsi", 
            $booking_type, $event_type, $event_date, $event_time, $event_location,
            $guest_count, $venue_type, $venue_id, $package_id, $contact_name,
            $contact_email, $contact_phone, $alternate_phone, $company_name,
            $preferred_contact, $special_instructions, $payment_method,
            $payment_status, $booking_status, $total_amount, $booking_id
        );
    } else {
        // Create new booking
        $stmt = $conn->prepare("INSERT INTO tbl_bookings (
            booking_reference, booking_type, event_type, event_date, event_time,
            event_location, guest_count, venue_type, venue_id, package_id,
            contact_name, contact_email, contact_phone, alternate_phone,
            company_name, preferred_contact, special_instructions, payment_method,
            payment_status, booking_status, total_amount
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssissssssssssssd", 
            $booking_reference, $booking_type, $event_type, $event_date, $event_time,
            $event_location, $guest_count, $venue_type, $venue_id, $package_id,
            $contact_name, $contact_email, $contact_phone, $alternate_phone,
            $company_name, $preferred_contact, $special_instructions, $payment_method,
            $payment_status, $booking_status, $total_amount
        );
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

// Helper functions to validate foreign keys
function isValidVenue($conn, $venue_id) {
    $stmt = $conn->prepare("SELECT venue_id FROM tbl_venues WHERE venue_id = ? AND status = 'available'");
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $isValid = $result->num_rows > 0;
    $stmt->close();
    return $isValid;
}

function isValidPackage($conn, $package_id) {
    $stmt = $conn->prepare("SELECT package_id FROM tbl_packages WHERE package_id = ? AND status = 'active'");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $isValid = $result->num_rows > 0;
    $stmt->close();
    return $isValid;
}
?>