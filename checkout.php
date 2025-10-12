<?php include __DIR__."/components/header.php"; ?>

    <!-- Page Header -->
    <div class="hero-section">
        <div class="container text-center text-white">
            <h1 class="display-4 fw-bold">Checkout</h1>
            <p class="lead">Complete your purchase and booking</p>
        </div>
    </div>

    <!-- Checkout Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Order Summary -->
            <div class="col-lg-4 order-lg-2 mb-4">
                <div class="card">
                    <div class="card-header bg-light-custom">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Elegant Wedding Package</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Elegant Centerpieces (x2)</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Chiavari Chairs (x20)</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery Fee</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 fw-bold fs-5">
                            <span>Total</span>
                            <span id="totalAmount">&#8369;9999.99</span>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Need Help?</h6>
                        <p class="card-text">Our event specialists are here to assist you with your booking.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-telephone-fill me-2 text-primary-custom"></i> +1 (555) 123-4567</li>
                            <li><i class="bi bi-envelope-fill me-2 text-primary-custom"></i> support@eventia.com</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="col-lg-8 order-lg-1">
                <!-- Wrap everything in one form -->
                <form id="checkOutForm" method="POST">
                    <!-- Payment Method Selection -->
                    <div class="card mb-4">
                        <div class="card-header bg-light-custom">
                            <h5 class="mb-0">Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="realPayment" value="real" checked>
                                <label class="form-check-label" for="realPayment">
                                    Real Payment (PayMongo)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="testPayment" value="test">
                                <label class="form-check-label" for="testPayment">
                                    Test Payment (Simulate - Localhost)
                                </label>
                            </div>
                            <input type="hidden" name="test_mode" id="testMode" value="false">
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light-custom">
                            <h5 class="mb-0">Event Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="eventType" class="form-label">Event Type</label>
                                    <select class="form-select" name="eventType" id="eventType" required>
                                        <option value="" selected disabled>Select event type</option>
                                        <option>Wedding</option>
                                        <option>Corporate Event</option>
                                        <option>Birthday Party</option>
                                        <option>Anniversary</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="eventDate" class="form-label">Event Date</label>
                                    <input type="date" class="form-control" name="eventDate" id="eventDate" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="guestCount" class="form-label">Number of Guests</label>
                                    <input type="number" class="form-control" name="guestCount" id="guestCount" min="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="eventLocation" class="form-label">Event Location</label>
                                    <input type="text" class="form-control" name="eventLocation" id="eventLocation" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="specialRequests" class="form-label">Special Requests</label>
                                <textarea class="form-control" name="specialRequests" id="specialRequests" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light-custom">
                            <h5 class="mb-0">Billing Information</h5>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="amount" id="amount" value="99999">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="firstName" id="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="lastName" id="lastName" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" id="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" id="address" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" id="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zip" class="form-label">ZIP</label>
                                    <input type="text" class="form-control" name="zip" id="zip" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#terms">Terms and Conditions</a> and <a href="#privacy">Privacy Policy</a>
                                </label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" name="checkOut" id="checkOut">Proceed to payment</button>
                            </div>
                            <p class="text-center mt-3 mb-0">
                                <small>Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.</small>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include __DIR__."/components/footer.php" ?>
<script>
    $("#checkOut").click(function(e){
    e.preventDefault();
    
    // Validate form first
    if(!$("#checkOutForm")[0].checkValidity()) {
        $("#checkOutForm")[0].reportValidity();
        return;
    }
    
    const isTestMode = $('#testMode').val() === 'true';
    
    console.log("Submitting payment - Test Mode:", isTestMode);
    
    // Show loading state
    const submitBtn = $('#checkOut');
    const originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...').prop('disabled', true);
    
    $.ajax({
        url: "paymongo-payment-method/create_payment.php",
        method: "POST",
        dataType: "json",
        data: $("#checkOutForm").serialize(),
        success: function(response){
            submitBtn.html(originalText).prop('disabled', false);
            
            console.log("Full response:", response);
            if(response.success && response.checkout_url) {
                // Store reference in sessionStorage for verification
                sessionStorage.setItem('paymentRef', response.reference);
                if(response.test_mode) {
                    sessionStorage.setItem('testMode', 'true');
                    console.log("Test payment created - Reference:", response.reference);
                    window.location.href = response.checkout_url;
                } else {
                    console.log("Real payment created - Reference:", response.reference);
                    
                    // Show instructions for real payment
                    Swal.fire({
                        title: 'Redirecting to PayMongo',
                        html: 'You will be redirected to PayMongo to complete your payment.<br><br>' +
                              '<strong>Important:</strong> After payment, please return to this site manually.<br>' +
                              'Your booking will be confirmed automatically.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Proceed to PayMongo',
                        cancelButtonText: 'Stay Here'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = response.checkout_url;
                        }
                    });
                }
            } else {
                Swal.fire('Error!', response.error || 'Failed to create payment', 'error');
            }
        },
        error: function(xhr, status, error) {
            submitBtn.html(originalText).prop('disabled', false);
            console.error('AJAX Error: ' + error);
            console.log('Status:', xhr.status);
            console.log('Response Text:', xhr.responseText);
            
            try {
                var errorResponse = JSON.parse(xhr.responseText);
                console.log('Error details:', errorResponse);
                Swal.fire('Error!', errorResponse.error || 'Network error: ' + error, 'error');
            } catch(e) {
                Swal.fire('Error!', 'Server error: ' + xhr.responseText, 'error');
            }
        }
    });
});
</script>