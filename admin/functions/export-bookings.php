<?php
include __DIR__ . '/../includes/db-config.php';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Booking Reference',
    'Event Type',
    'Event Date',
    'Customer Name',
    'Customer Email',
    'Guest Count',
    'Total Amount',
    'Payment Status',
    'Booking Status',
    'Created Date'
]);

// Build query based on filters
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';
$dateFilter = $_GET['date'] ?? '';

$query = "SELECT b.booking_reference, b.event_type, b.event_date, 
                 b.contact_name, b.contact_email, b.guest_count,
                 b.total_amount, b.payment_status, b.booking_status, b.created_at
          FROM tbl_bookings b WHERE 1=1";

$params = [];
$types = '';

if (!empty($searchTerm)) {
    $query .= " AND (b.booking_reference LIKE ? OR b.contact_name LIKE ? OR b.contact_email LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $types .= 'sss';
}

if ($statusFilter !== 'all') {
    $query .= " AND b.booking_status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($typeFilter !== 'all') {
    $query .= " AND b.booking_type = ?";
    $params[] = $typeFilter;
    $types .= 's';
}

if (!empty($dateFilter)) {
    $query .= " AND b.event_date = ?";
    $params[] = $dateFilter;
    $types .= 's';
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['booking_reference'],
        $row['event_type'],
        $row['event_date'],
        $row['contact_name'],
        $row['contact_email'],
        $row['guest_count'],
        $row['total_amount'],
        $row['payment_status'],
        $row['booking_status'],
        $row['created_at']
    ]);
}

$stmt->close();
$conn->close();
?>