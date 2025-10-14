<?php include __DIR__."/components/header.php"; ?>
<?php
require_once __DIR__ . '/database/config.php';

$success = isset($_GET['success']) && $_GET['success'] === 'true';
$ref = $_GET['ref'] ?? '';
$user_id = $_SESSION['id'] ?? null;
$price = $_SESSION['amount'] ?? 0;

if ($success) {
    $status = 'PAID';
    $title = "Payment Successful!";
    $message = "Thank you for your payment. Your booking has been confirmed.";
    $alertClass = "alert-success";
    $icon = "bi-check-circle";
    
    // ✅ SAVE BOOKING TO DATABASE AFTER SUCCESSFUL PAYMENT
    if (isset($_SESSION['pending_booking'])) {
        $bookingData = $_SESSION['pending_booking'];
        
        try {
            // Insert into tbl_bookings
            $stmt = $conn->prepare("
                INSERT INTO tbl_bookings (
                    booking_reference, user_id, booking_type, event_type, 
                    package_name, venue_type, venue_id, event_location, full_address,
                    event_date, event_time, contact_name, contact_email, contact_phone,
                    guest_count, special_instructions, total_amount, booking_status, payment_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $contact_name = $bookingData['firstName'] . ' ' . $bookingData['lastName'];
            $booking_status = 'confirmed';
            $payment_status = 'paid';
            
            $stmt->bind_param(
                "sissisisssssssisdss", 
                $ref, // booking_reference
                $user_id, // user_id
                $bookingData['booking_type'], // booking_type
                $bookingData['eventType'], // event_type
                $bookingData['package'], // package_name
                $bookingData['venue_type'], // venue_type
                $bookingData['venue_id'], // venue_id
                $bookingData['event_location'], // event_location
                $bookingData['full_address'], // full_address
                $bookingData['event_date'], // event_date
                $bookingData['event_time'], // event_time
                $contact_name, // contact_name
                $bookingData['email'], // contact_email
                $bookingData['phone'], // contact_phone
                $bookingData['guest_count'], // guest_count
                $bookingData['special_instructions'], // special_instructions
                $bookingData['amount'], // total_amount
                $booking_status, // booking_status
                $payment_status // payment_status
            );
            
            if ($stmt->execute()) {
                $booking_id = $conn->insert_id;
                
                // ✅ Save services if any
                if (!empty($bookingData['services'])) {
                    saveBookingServices($conn, $booking_id, $bookingData['services'], $bookingData['customizations']);
                }
                
                // Clear pending booking session
                unset($_SESSION['pending_booking']);
                
                $message .= " Your booking reference is: " . $ref;
            } else {
                $message .= " Warning: Could not save booking details. Please contact support with reference: " . $ref;
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Booking save error: " . $e->getMessage());
            $message .= " Error saving booking. Please contact support with reference: " . $ref;
        }
    }
    
} else {
    $status = 'CANCELLED';
    $title = "Payment Cancelled";
    $message = "Your payment was cancelled. You can try again anytime.";
    $alertClass = "alert-warning";
    $icon = "bi-x-circle";
    
    // Clear pending booking on cancellation
    if (isset($_SESSION['pending_booking'])) {
        unset($_SESSION['pending_booking']);
    }
}

// Function to save booking services
function saveBookingServices($conn, $booking_id, $services, $customizations) {
    // Get service details from database
    $serviceStmt = $conn->prepare("SELECT service_id, service_name, base_price FROM tbl_services WHERE service_name = ?");
    
    foreach ($services as $service_name) {
        $serviceStmt->bind_param("s", $service_name);
        $serviceStmt->execute();
        $result = $serviceStmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $service_id = $row['service_id'];
            $base_price = $row['base_price'];
            
            // Get customization if exists
            $customization_json = null;
            if (isset($customizations[$service_id])) {
                $customization_json = json_encode($customizations[$service_id]);
            }
            
            // Insert service
            $insertStmt = $conn->prepare("
                INSERT INTO tbl_booking_services (booking_id, service_id, service_name, base_price, final_price, customization_details)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $insertStmt->bind_param(
                "iisdds",
                $booking_id,
                $service_id,
                $service_name,
                $base_price,
                $base_price, // final_price same as base_price for now
                $customization_json
            );
            
            $insertStmt->execute();
            $insertStmt->close();
        }
    }
    
    $serviceStmt->close();
}

// Insert into transactions table
$insertsql = $conn->prepare("INSERT INTO tbl_transactions (user_id, ref_id, status, price) VALUES (?, ?, ?, ?)");
$insertsql->bind_param("issi", $user_id, $ref, $status, $price);
if ($insertsql->execute()) {
    if (isset($_SESSION['amount'])) {
        unset($_SESSION['amount']);
    }
}
$insertsql->close();
?>  

<!-- Page Header -->
<div class="hero-section">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold"><?php echo $success ? 'Payment Successful' : 'Payment Cancelled'; ?></h1>
        <p class="lead"><?php echo $success ? 'Thank you for your booking!' : 'No worries, you can try again.'; ?></p>
    </div>
</div>

<!-- Status Content -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi <?php echo $icon; ?> <?php echo $success ? 'text-success' : 'text-warning'; ?>" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="card-title mb-3"><?php echo $title; ?></h3>
                    <p class="card-text mb-4">
                        <?php echo $message; ?>
                    </p>
                    
                    <?php if ($ref): ?>
                    <div class="alert <?php echo $alertClass; ?> mb-4" role="alert">
                        <strong>Reference ID:</strong> <?php echo htmlspecialchars($ref); ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary btn-lg">Return to Home</a>
                        <?php if (!$success): ?>
                            <a href="self_booking.php" class="btn btn-outline-primary">Try Again</a>
                        <?php else: ?>
                            <a href="bookings.php" class="btn btn-outline-success">View My Bookings</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__."/components/footer.php"; ?>