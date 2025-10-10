<?php include __DIR__."/components/header.php"; ?>

    <!-- Additional CSS -->
    <link rel="stylesheet" href="css/booking-improvements.css">

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Choose Your Booking Experience</h1>
            <p class="lead mb-4">Select the perfect way to plan your event</p>
        </div>
    </div>

    <!-- Booking Options -->
    <div class="container my-5">
        <div class="row justify-content-center g-4">
            
            <!-- Self-Book Option -->
            <div class="col-lg-5 col-md-6">
                <div class="card booking-option-card h-100">
                    <div class="card-body text-center p-5">
                        <div class="booking-icon mb-4">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h2 class="card-title mb-3">Self-Book</h2>
                        <p class="card-text mb-4 text-muted">Take control of your event planning. Browse our services, customize your package, and book everything yourself at your own pace.</p>
                        
                        <ul class="list-unstyled text-start mb-4">
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Browse all available services</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Customize your own package</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Choose your own venue</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Instant booking confirmation</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Manage everything online</span>
                            </li>
                        </ul>
                        
                        <div class="mt-auto">
                            <p class="text-primary fw-bold mb-3">Perfect for those who know exactly what they want</p>
                            <a href="self_booking.php" class="btn btn-primary btn-lg w-100">
                                Start Self-Booking <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guided Book Option -->
            <div class="col-lg-5 col-md-6">
                <div class="card booking-option-card h-100 featured-card">
                    <div class="featured-badge">
                        <i class="bi bi-star-fill me-1"></i> Recommended
                    </div>
                    <div class="card-body text-center p-5">
                        <div class="booking-icon mb-4">
                            <i class="bi bi-people"></i>
                        </div>
                        <h2 class="card-title mb-3">Guided Booking</h2>
                        <p class="card-text mb-4 text-muted">Let our expert event planners guide you through the entire process. Get personalized recommendations and professional assistance every step of the way.</p>
                        
                        <ul class="list-unstyled text-start mb-4">
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Personal event consultant</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Customized recommendations</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Venue selection assistance</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Budget optimization</span>
                            </li>
                            <li class="benefit-item">
                                <i class="bi bi-check-circle-fill benefit-icon"></i>
                                <span>Priority support</span>
                            </li>
                        </ul>
                        
                        <div class="mt-auto">
                            <p class="text-primary fw-bold mb-3">Ideal for stress-free, expertly planned events</p>
                            <a href="guided_booking.php" class="btn btn-primary btn-lg w-100">
                                Get Expert Help <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Comparison Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="comparison-section text-center">
                    <h3 class="section-title d-inline-block">Still not sure which to choose?</h3>
                    <p class="text-muted mb-4">Compare both options to find what works best for you</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <div class="comparison-badge">
                            <i class="bi bi-clock me-2"></i>
                            <span>Self-Book: Faster Process</span>
                        </div>
                        <div class="comparison-badge">
                            <i class="bi bi-lightbulb me-2"></i>
                            <span>Guided: Expert Insights</span>
                        </div>
                        <div class="comparison-badge">
                            <i class="bi bi-wallet2 me-2"></i>
                            <span>Self-Book: Full Control</span>
                        </div>
                        <div class="comparison-badge">
                            <i class="bi bi-shield-check me-2"></i>
                            <span>Guided: Stress-Free Planning</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Book With Us -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="section-title text-center">Why Book With Eventia?</h3>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="service-icon mb-3">
                            <i class="bi bi-award"></i>
                        </div>
                        <h5>10+ Years Experience</h5>
                        <p class="text-muted mb-0">Trusted by thousands of clients for memorable events</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="service-icon mb-3">
                            <i class="bi bi-stars"></i>
                        </div>
                        <h5>Premium Venues</h5>
                        <p class="text-muted mb-0">Access to exclusive and beautiful event locations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="service-icon mb-3">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted mb-0">We're here to help you every step of the way</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__."/components/footer.php" ?>