
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
    <title>Eventia - Your Event Planning Solution</title>
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="packages.php">Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
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
            <h1 class="display-4 fw-bold mb-4">Create Unforgettable Events</h1>
            <p class="lead mb-4">From intimate gatherings to grand celebrations, we make your events memorable</p>
        </div>
    </div>

    <!-- Featured Packages -->
    <div class="container my-5">
        <h2 class="section-title">Featured Packages</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card package-card">
                    <div class="card-header">Wedding Package</div>
                    <div class="card-body">
                        <h5 class="card-title">Elegant Wedding</h5>
                        <p class="card-text">Complete wedding planning with venue decoration, catering, and photography.</p>
                        <div class="mb-3">
                            <span class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                            </span>
                            <span class="ms-1">4.5/5</span>
                        </div>
                        <h4 class="text-primary">₱140,500.00</h4>
                        <a href="packages.html" class="btn btn-primary mt-2">View Details</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card package-card">
                    <div class="card-header">Corporate Package</div>
                    <div class="card-body">
                        <h5 class="card-title">Business Events</h5>
                        <p class="card-text">Professional event planning for conferences, seminars, and corporate gatherings.</p>
                        <div class="mb-3">
                            <span class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                            </span>
                            <span class="ms-1">4/5</span>
                        </div>
                        <h4 class="text-primary">₱108,190.00</h4>
                        <a href="packages.html" class="btn btn-primary mt-2">View Details</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card package-card">
                    <div class="card-header">Birthday Package</div>
                    <div class="card-body">
                        <h5 class="card-title">Kids Birthday</h5>
                        <p class="card-text">Fun and creative birthday parties with themes, entertainment, and catering.</p>
                        <div class="mb-3">
                            <span class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <span class="ms-1">5/5</span>
                        </div>
                        <h4 class="text-primary">₱50,000</h4>
                        <a href="packages.html" class="btn btn-primary mt-2">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Team -->
    <div class="container my-5">
        <h2 class="section-title">Our Team</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/kristan.jpg" alt="Team Member" class="img-fluid">
                    <h5>Kristan Almario</h5>
                    <p>Founder & CEO</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/lance.jpg" alt="Team Member" class="img-fluid">
                    <h5>Lance Villanueva</h5>
                    <p>Event Planner</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/archie.jpeg" alt="Team Member" class="img-fluid">
                    <h5>Archie De Leon</h5>
                    <p>Design Director</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/aldis.jpg" alt="Team Member" class="img-fluid">
                    <h5>Aldis Miranda </h5>
                    <p>Marketing Manager</p>
                </div>
            </div>
        </div>
    </div>

    <!-- About Us -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="section-title">Why Choose Eventia?</h2>
            <p class="lead">We started Eventia with a simple mission: to remove the stress from event planning so you can focus on enjoying your special moments. Our team of experienced planners, designers, and coordinators work together to create seamless, memorable events tailored to your vision and budget.</p>
            
        </div>
    </div>

    <!-- Testimonials -->
    <div class="container my-5">
        <h2 class="section-title">Client Reviews</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://randomuser.me/api/portraits/women/28.jpg" alt="Client" class="testimonial-img me-3">
                        <div>
                            <h5 class="mb-0">Jennifer L.</h5>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mb-0">"Eventia made my wedding day absolutely perfect! Their attention to detail and professional service exceeded all my expectations."</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://randomuser.me/api/portraits/men/22.jpg" alt="Client" class="testimonial-img me-3">
                        <div>
                            <h5 class="mb-0">Robert T.</h5>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mb-0">"The corporate event they organized for our company was flawless. Everything from venue selection to catering was perfect."</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://randomuser.me/api/portraits/women/43.jpg" alt="Client" class="testimonial-img me-3">
                        <div>
                            <h5 class="mb-0">Amanda P.</h5>
                            <div class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mb-0">"My daughter's birthday party was magical thanks to Eventia. The theme execution was incredible and the children had a wonderful time."</p>
                </div>
            </div>
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