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
        SELECT booking_reference, contact_name, event_type, event_date, total_amount, booking_status
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

// NEW: Get pending bookings count
function getPendingBookingsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings WHERE booking_status = 'pending'");
    return $result->fetch_assoc()['total'] ?? 0;
}

// NEW: Get today's revenue from both bookings and transactions
function getTodaysRevenue($conn) {
    $today = date('Y-m-d');
    
    // Revenue from bookings (paid today)
    $booking_revenue = 0;
    $result = $conn->query("
        SELECT COALESCE(SUM(total_amount), 0) as total 
        FROM tbl_bookings 
        WHERE payment_status = 'paid' 
        AND DATE(created_at) = '$today'
    ");
    if ($result) {
        $booking_revenue = $result->fetch_assoc()['total'] ?? 0;
    }
    
    // Revenue from transactions (paid today)
    $transaction_revenue = 0;
    $result = $conn->query("
        SELECT COALESCE(SUM(price), 0) as total 
        FROM tbl_transactions 
        WHERE status = 'PAID' 
        AND DATE(date_time) = '$today'
    ");
    if ($result) {
        $transaction_revenue = $result->fetch_assoc()['total'] ?? 0;
    }
    
    return $booking_revenue + $transaction_revenue;
}

// NEW: Get total paid bookings count for success rate calculation
function getPaidBookingsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings WHERE payment_status = 'paid'");
    return $result->fetch_assoc()['total'] ?? 0;
}

?>