<?php
session_start();
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Verify CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_booking_details':
            getBookingDetails($conn);
            break;
        case 'save':
            saveBooking($conn);
            break;
        case 'update_status':
            updateBookingStatus($conn);
            break;
        case 'bulk_update_status':
            bulkUpdateBookingStatus($conn);
            break;
        case 'delete':
            deleteBooking($conn);
            break;
        case 'bulk_delete':
            bulkDeleteBookings($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getBookingDetails($conn) {
    $bookingId = $_GET['booking_id'] ?? 0;
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT b.*, 
               u.first_name, u.last_name, u.email as user_email, u.phone as user_phone,
               v.venue_name, p.package_name,
               COUNT(bs.booking_service_id) as service_count
        FROM tbl_bookings b
        LEFT JOIN tbl_users u ON b.user_id = u.user_id
        LEFT JOIN tbl_venues v ON b.venue_id = v.venue_id
        LEFT JOIN tbl_packages p ON b.package_id = p.package_id
        LEFT JOIN tbl_booking_services bs ON b.booking_id = bs.booking_id
        WHERE b.booking_id = ?
        GROUP BY b.booking_id
    ");
    
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    if ($booking) {
        echo json_encode(['success' => true, 'data' => $booking]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
    }
}

function saveBooking($conn) {
    $bookingId = $_POST['booking_id'] ?? 0;
    $isNew = empty($bookingId);
    
    // Validate required fields
    $required = ['booking_reference', 'booking_type', 'event_type', 'event_date', 'contact_name', 'contact_email', 'contact_phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Prepare data according to your database schema
    $data = [
        'booking_reference' => $_POST['booking_reference'],
        'booking_type' => $_POST['booking_type'],
        'event_type' => $_POST['event_type'],
        'event_date' => $_POST['event_date'],
        'event_time' => $_POST['event_time'] ?? '',
        'event_location' => $_POST['event_location'] ?? '',
        'guest_count' => $_POST['guest_count'] ?? 0,
        'venue_type' => $_POST['venue_type'] ?? 'own',
        'venue_id' => !empty($_POST['venue_id']) ? $_POST['venue_id'] : null,
        'package_id' => !empty($_POST['package_id']) ? $_POST['package_id'] : null,
        'contact_name' => $_POST['contact_name'],
        'contact_email' => $_POST['contact_email'],
        'contact_phone' => $_POST['contact_phone'],
        'alternate_phone' => $_POST['alternate_phone'] ?? null,
        'company_name' => $_POST['company_name'] ?? null,
        'preferred_contact' => $_POST['preferred_contact'] ?? 'Any',
        'special_instructions' => $_POST['special_instructions'] ?? null,
        'total_amount' => $_POST['total_amount'] ?? 0,
        'payment_method' => $_POST['payment_method'] ?? 'GCash',
        'payment_status' => $_POST['payment_status'] ?? 'pending',
        'booking_status' => $_POST['booking_status'] ?? 'pending'
    ];
    
    // Handle user_id - for admin-created bookings, we might not have a user
    $userId = null;
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $userId = $_POST['user_id'];
    }
    
    if ($isNew) {
        // Insert new booking
        $columns = array_keys($data);
        if ($userId !== null) {
            $columns[] = 'user_id';
            $data['user_id'] = $userId;
        }
        
        $columnNames = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        
        $stmt = $conn->prepare("INSERT INTO tbl_bookings ($columnNames) VALUES ($placeholders)");
        
        // Build types string and values array
        $types = '';
        $values = [];
        foreach ($columns as $column) {
            $value = $data[$column];
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
    } else {
        // Update existing booking
        $updates = [];
        $types = '';
        $values = [];
        
        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        // Add user_id if provided
        if ($userId !== null) {
            $updates[] = "user_id = ?";
            $types .= 'i';
            $values[] = $userId;
        }
        
        $updates = implode(', ', $updates);
        $types .= 'i';
        $values[] = $bookingId;
        
        $stmt = $conn->prepare("UPDATE tbl_bookings SET $updates WHERE booking_id = ?");
        $stmt->bind_param($types, ...$values);
    }
    
    if ($stmt->execute()) {
        $message = $isNew ? 'Booking created successfully' : 'Booking updated successfully';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function updateBookingStatus($conn) {
    $bookingId = $_POST['booking_id'] ?? 0;
    $bookingStatus = $_POST['booking_status'] ?? 'pending';
    $paymentStatus = $_POST['payment_status'] ?? 'pending';
    $notes = $_POST['notes'] ?? null;
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE tbl_bookings SET booking_status = ?, payment_status = ? WHERE booking_id = ?");
    $stmt->bind_param("ssi", $bookingStatus, $paymentStatus, $bookingId);
    
    if ($stmt->execute()) {
        // Log the status change (you might want to create tbl_booking_logs table)
        // if ($notes) {
        //     $logStmt = $conn->prepare("INSERT INTO tbl_booking_logs (booking_id, action, notes, admin_id) VALUES (?, 'status_update', ?, ?)");
        //     $logStmt->bind_param("isi", $bookingId, $notes, $_SESSION['admin_id']);
        //     $logStmt->execute();
        //     $logStmt->close();
        // }
        
        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function bulkUpdateBookingStatus($conn) {
    $bookingIds = json_decode($_POST['booking_ids'] ?? '[]', true);
    $status = $_POST['status'] ?? 'pending';
    
    if (empty($bookingIds)) {
        echo json_encode(['success' => false, 'message' => 'No bookings selected']);
        return;
    }
    
    // Create placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
    $types = str_repeat('i', count($bookingIds));
    
    $stmt = $conn->prepare("UPDATE tbl_bookings SET booking_status = ? WHERE booking_id IN ($placeholders)");
    $stmt->bind_param("s" . $types, $status, ...$bookingIds);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => count($bookingIds) . ' booking(s) updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating bookings: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function deleteBooking($conn) {
    $bookingId = $_POST['booking_id'] ?? 0;
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete related booking services first
        $stmt1 = $conn->prepare("DELETE FROM tbl_booking_services WHERE booking_id = ?");
        $stmt1->bind_param("i", $bookingId);
        $stmt1->execute();
        $stmt1->close();
        
        // Delete the booking
        $stmt2 = $conn->prepare("DELETE FROM tbl_bookings WHERE booking_id = ?");
        $stmt2->bind_param("i", $bookingId);
        $stmt2->execute();
        $stmt2->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error deleting booking: ' . $e->getMessage()]);
    }
}

function bulkDeleteBookings($conn) {
    $bookingIds = json_decode($_POST['booking_ids'] ?? '[]', true);
    
    if (empty($bookingIds)) {
        echo json_encode(['success' => false, 'message' => 'No bookings selected']);
        return;
    }
    
    $conn->begin_transaction();
    
    try {
        $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
        $types = str_repeat('i', count($bookingIds));
        
        // Delete related booking services
        $stmt1 = $conn->prepare("DELETE FROM tbl_booking_services WHERE booking_id IN ($placeholders)");
        $stmt1->bind_param($types, ...$bookingIds);
        $stmt1->execute();
        $stmt1->close();
        
        // Delete the bookings
        $stmt2 = $conn->prepare("DELETE FROM tbl_bookings WHERE booking_id IN ($placeholders)");
        $stmt2->bind_param($types, ...$bookingIds);
        $stmt2->execute();
        $stmt2->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => count($bookingIds) . ' booking(s) deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error deleting bookings: ' . $e->getMessage()]);
    }
}
?>