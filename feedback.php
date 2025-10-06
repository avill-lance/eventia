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
    <title>Feedback - Eventia</title>
    <link rel="icon" type="image/png" href="assets/Logo_BG.png">
    <link rel="stylesheet" href="css/bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation (Same as index.html) -->
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
                        <a class="nav-link active" href="feedback.php">Feedback</a>
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
            <h1 class="display-4 fw-bold mb-4">Share Your Experience</h1>
            <p class="lead mb-4">Your feedback helps us improve our services</p>
        </div>
    </div>

    <!-- Feedback Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Feedback Form -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Submit Feedback</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label for="feedbackType" class="form-label">Feedback Type</label>
                                <select class="form-select" id="feedbackType" required>
                                    <option value="" selected disabled>Select feedback type</option>
                                    <option>Event Service</option>
                                    <option>Product Review</option>
                                    <option>Website Experience</option>
                                    <option>Customer Support</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="orderReference" class="form-label">Order Reference (Optional)</label>
                                <input type="text" class="form-control" id="orderReference" placeholder="If applicable">
                            </div>
                            <div class="mb-3">
                                <label for="rating" class="form-label">Overall Rating</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating1" value="1">
                                        <label class="form-check-label" for="rating1">1</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating2" value="2">
                                        <label class="form-check-label" for="rating2">2</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating3" value="3">
                                        <label class="form-check-label" for="rating3">3</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating4" value="4">
                                        <label class="form-check-label" for="rating4">4</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating5" value="5" checked>
                                        <label class="form-check-label" for="rating5">5</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="feedbackTitle" class="form-label">Title</label>
                                <input type="text" class="form-control" id="feedbackTitle" required>
                            </div>
                            <div class="mb-3">
                                <label for="feedbackText" class="form-label">Your Feedback</label>
                                <textarea class="form-control" id="feedbackText" rows="5" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="photoUpload" class="form-label">Upload Photos (Optional)</label>
                                <input type="file" class="form-control" id="photoUpload" multiple accept="image/*">
                                <div class="form-text">You can upload up to 5 photos. Maximum file size: 5MB each.</div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="permission">
                                <label class="form-check-label" for="permission">
                                    I give permission to Eventia to use my feedback and photos for marketing purposes
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Feedback</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Feedback -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Recent Feedback</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://randomuser.me/api/portraits/women/28.jpg" alt="User" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <h6 class="mb-0">Jennifer L.</h6>
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
                        <hr>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://randomuser.me/api/portraits/men/22.jpg" alt="User" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <h6 class="mb-0">Robert T.</h6>
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
                        <hr>
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://randomuser.me/api/portraits/women/43.jpg" alt="User" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <h6 class="mb-0">Amanda P.</h6>
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

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Why Your Feedback Matters</h5>
                        <p class="card-text">We value your opinion and use your feedback to:</p>
                        <ul class="mb-0">
                            <li>Improve our services and products</li>
                            <li>Train our team members</li>
                            <li>Develop new features and offerings</li>
                            <li>Ensure customer satisfaction</li>
                        </ul>
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