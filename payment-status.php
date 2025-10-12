<?php
$success = isset($_GET['success']) && $_GET['success'] === 'true';
$ref = $_GET['ref'] ?? '';

if ($success) {
    $title = "Payment Successful!";
    $message = "Thank you for your payment. Your booking has been confirmed.";
    $alertClass = "alert-success";
    $icon = "bi-check-circle";
} else {
    $title = "Payment Cancelled";
    $message = "Your payment was cancelled. You can try again anytime.";
    $alertClass = "alert-warning";
    $icon = "bi-x-circle";
}
?>

<?php include __DIR__."/components/header.php"; ?>

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
                                <a href="checkout.php" class="btn btn-outline-primary">Try Again</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__."/components/footer.php" ?>