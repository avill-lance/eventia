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
    <title>About Us - Eventia</title>
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
                        <a class="nav-link active" href="about.php">About</a>
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
            <h1 class="display-4 fw-bold mb-4">About Eventia</h1>
            <p class="lead mb-4">Learn about our story, mission, and the team behind your unforgettable events</p>
        </div>
    </div>

    <!-- Our Story -->
    <div class="container my-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="section-title">Our Story</h2>
                <p class="lead">From humble beginnings to creating magical events</p>
                <p>Eventia was founded in 2010 with a simple mission: to create unforgettable events that bring people together. What started as a small wedding planning service has grown into a comprehensive event management company serving clients across the region.</p>
                <p>Our journey began when our founder, Kristan Almario, noticed a gap in the market for personalized, stress-free event planning. With a passion for creating memorable experiences, Kristan assembled a team of talented professionals who shared the same vision.</p>
            </div>
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?ixlib=rb-4.0.3&auto=format&fit=crop&w=1050&q=80" alt="Our Story" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>

    <!-- Our Mission -->
    <div class="bg-light-custom py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 order-md-2">
                    <h2 class="section-title">Our Mission</h2>
                    <p class="lead">Creating exceptional experiences through meticulous planning and creative vision</p>
                    <p>At Eventia, we believe that every event tells a story. Our mission is to help you tell yours through carefully crafted experiences that reflect your personality, values, and vision.</p>
                    <p>We're committed to excellence in every detail, from the initial consultation to the final execution. Our team works tirelessly to ensure that your event not only meets but exceeds your expectations.</p>
                </div>
                <div class="col-md-6 order-md-1">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1050&q=80" alt="Our Mission" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>

    <!-- Our Timeline -->
    <div class="container my-5">
        <h2 class="section-title text-center">Our Journey</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5>2010</h5>
                    <p>Eventia was founded with a focus on wedding planning services</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5>2013</h5>
                    <p>Expanded to corporate events and launched our first package offerings</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5>2015</h5>
                    <p>Opened our second office and reached 1,000 events milestone</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5>2018</h5>
                    <p>Launched our online platform and introduced the Eventia Shop</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5>2020</h5>
                    <p>Pivoted to virtual events during pandemic, serving clients globally</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5>2023</h5>
                    <p>Celebrated 5,000+ events and expanded team to 30+ professionals</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Team -->
    <div class="container my-5">
        <h2 class="section-title text-center">Our Team</h2>
        <p class="text-center mb-5">Meet the passionate professionals who make your events extraordinary</p>
        <div class="row">
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/kristan.jpg" alt="Team Member" class="img-fluid">
                    <h5>Kristan Almario</h5>
                    <p>Founder & CEO</p>
                    <div class="d-flex justify-content-center">
                        <a href="#" class="text-primary-custom me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-primary-custom"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/lance.jpg" alt="Team Member" class="img-fluid">
                    <h5>Lance Villanueva</h5>
                    <p>Event Planner</p>
                    <div class="d-flex justify-content-center">
                        <a href="#" class="text-primary-custom me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-primary-custom"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/archie.jpeg" alt="Team Member" class="img-fluid">
                    <h5>Archie De Leon</h5>
                    <p>Design Director</p>
                    <div class="d-flex justify-content-center">
                        <a href="#" class="text-primary-custom me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-primary-custom"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/aldis.jpg" alt="Team Member" class="img-fluid">
                    <h5>Aldis Miranda</h5>
                    <p>Marketing Manager</p>
                    <div class="d-flex justify-content-center">
                        <a href="#" class="text-primary-custom me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-primary-custom"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="contact.html" class="btn btn-primary">Meet the Whole Team</a>
        </div>
    </div>

    <!-- Values -->
    <div class="bg-light-custom py-5">
        <div class="container">
            <h2 class="section-title text-center">Our Values</h2>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="bi bi-heart-fill display-4 text-primary-custom mb-3"></i>
                            <h5>Passion</h5>
                            <p>We genuinely love what we do, and it shows in every event we create.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="bi bi-lightbulb-fill display-4 text-primary-custom mb-3"></i>
                            <h5>Innovation</h5>
                            <p>We constantly seek new ideas and approaches to make your event unique.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="bi bi-shield-check display-4 text-primary-custom mb-3"></i>
                            <h5>Reliability</h5>
                            <p>We deliver on our promises, ensuring your event runs smoothly from start to finish.</p>
                        </div>
                    </div>
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