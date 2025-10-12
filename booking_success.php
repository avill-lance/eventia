<?php
session_start();
include __DIR__."/components/header.php";

$booking_reference = $_GET['reference'] ?? '';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="display-4 fw-bold text-success mb-3">Booking Confirmed!</h1>
                    <p class="lead mb-4">Thank you for your booking. We're excited to help make your event special!</p>
                    
                    <?php if ($booking_reference): ?>
                    <div class="alert alert-info mx-auto" style="max-width: 400px;">
                        <h5 class="alert-heading">Your Booking Reference</h5>
                        <p class="mb-0 fs-4 fw-bold"><?php echo htmlspecialchars($booking_reference); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <p class="text-muted mb-4">
                            We've sent a confirmation email with all the details. Our team will contact you within 24 hours to discuss next steps.
                        </p>
                        
                        <div class="d-flex justify-content-center gap-3">
                            <a href="index.php" class="btn btn-primary">Return to Home</a>
                            <a href="self_booking.php" class="btn btn-outline-primary">Book Another Event</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h5>What happens next?</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Our event coordinator will contact you within 24 hours</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i> We'll confirm venue availability and service details</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i> You'll receive a detailed event proposal</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Final payment arrangements will be confirmed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__."/components/footer.php"; ?>