<?php

function isAdmin(){
    if(!isset($_SESSION['email']) || !isset($_SESSION['first_name']) || !isset($_SESSION['last_name'])){
        header("Location: dashboard.php");
        exit(0);
    }
}

// Statistics functions
function getServicesCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_services WHERE status = 'active'");
    return $result->fetch_assoc()['total'] ?? 0;
}

function getRecentBookings($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT booking_reference, contact_name, event_type, event_date, total_amount 
        FROM tbl_bookings 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getPendingInquiries($conn) {
    // Since we don't have an inquiries table yet, return 0 or use bookings as placeholder
    return 0; // Placeholder - can be updated when inquiries table exists
}

function getActiveVenues($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_venues WHERE status = 'available'");
    return $result->fetch_assoc()['total'] ?? 0;
}

?>