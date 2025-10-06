<?php
    // ### Establish Session ###
    include  __DIR__ . "/functions/session.php";
    
    // ### Include functions compilation ###
    include  __DIR__ . "/functions/FunctionCompilation.php";
    isLoggedIn();
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Eventia</title>
    <link rel="icon" type="image/png" href="assets/Logo_BG.png">
    <link rel="stylesheet" href="css/bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="assets/EventiaLogo.png" alt="" width="50" height="45" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="packages.php">Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blogs.php">Blogs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex ms-auto align-items-center">
                    <a href="cart.php" class="cart-btn-v3 me-3">
                        <i class="bi bi-cart3 cart-icon"></i>
                        <span class="badge">3</span>
                    </a>
                    <a class="nav-link p-0" id="userProfile" href="profile.php">Welcome, <?php echo $_SESSION["first_name"]; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Our Services</h1>
            <p class="lead mb-4">Comprehensive event planning solutions tailored to your unique needs</p>
        </div>
    </div>

    <!-- Additional Services -->
    <div class="bg-light pb-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-palette service-icon"></i>
                            <h5 class="card-title">Event Design</h5>
                            <p class="card-text">From concept to execution, our design team creates visually stunning events that reflect your vision and personality.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-geo-alt service-icon"></i>
                            <h5 class="card-title">Venue Selection</h5>
                            <p class="card-text">We have relationships with the best venues and can help you find the perfect location for your event.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-cup-hot service-icon"></i>
                            <h5 class="card-title">Catering Coordination</h5>
                            <p class="card-text">We work with top caterers to create menus that delight your guests and accommodate all dietary needs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-music-note-beamed service-icon"></i>
                            <h5 class="card-title">Entertainment Booking</h5>
                            <p class="card-text">From DJs to live bands, speakers to performers, we find the perfect entertainment for your event.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-flower1 service-icon"></i>
                            <h5 class="card-title">Floral & Decor</h5>
                            <p class="card-text">Our floral designers create beautiful arrangements and decor elements that transform your venue.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-camera service-icon"></i>
                            <h5 class="card-title">Photography & Videography</h5>
                            <p class="card-text">We connect you with talented photographers and videographers to capture your event's special moments.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Section -->
    <div class="container mt-1">
        <h2 class="section-title text-center">Our Process</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h5>Consultation</h5>
                    <p>We begin with a detailed consultation to understand your vision, needs, and budget.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h5>Planning</h5>
                    <p>Our team creates a customized plan and timeline for your event.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h5>Execution</h5>
                    <p>We handle all the details and coordinate with vendors to bring your event to life.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="process-step">
                    <div class="step-number">4</div>
                    <h5>Follow-up</h5>
                    <p>After the event, we ensure everything is wrapped up perfectly and gather feedback.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="packages.html" class="btn btn-primary">Start Planning Your Event</a>
        </div>
    </div>

<!-- Footer -->
    <footer class="d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Eventia</h5>
                    <p>Creating memorable events with precision and creativity. Let us turn your vision into reality.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.html" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="packages.html" class="text-light text-decoration-none">Packages</a></li>
                        <li><a href="#services" class="text-light text-decoration-none">Services</a></li>
                        <li><a href="shop.html" class="text-light text-decoration-none">Shop</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#faq" class="text-light text-decoration-none">FAQ</a></li>
                        <li><a href="#contact" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>
            <hr class="mt-0 mb-4">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; 2025 Eventia. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

<script src="js/js/bootstrap.bundle.min.js"></script>
</body>
</html>