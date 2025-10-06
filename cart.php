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
    <title>Shopping Cart - Eventia</title>
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
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex ms-auto align-items-center">
                    <a class="nav-link p-0" id="userProfile" href="profile.php">Welcome, <?php echo $_SESSION["first_name"]; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="hero-section">
        <div class="container text-center text-white">
            <h1 class="display-4 fw-bold">Your Cart</h1>
            <p class="lead">Review your selected items and events</p>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-light-custom">
                        <h5 class="mb-0">Event Packages</h5>
                    </div>
                    <div class="card-body">
                        <!-- Package Item -->
                        <div class="row align-items-center mb-4">
                            <div class="col-md-2">
                                <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" class="img-fluid rounded shadow" alt="Package">
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">Elegant Wedding Package</h5>
                                <p class="mb-1 text-muted">Complete wedding planning with venue decoration, catering, and photography.</p>
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                    <span class="ms-1">4.5/5</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="updateQuantity(this, -1)">-</button>
                                    <input type="text" class="form-control text-center" value="1" readonly>
                                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="updateQuantity(this, 1)">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <h5 class="mb-0">&#8369;9999.99</h5>
                                <button class="btn btn-sm btn-link text-danger p-0" onclick="removeItem(this)">Remove</button>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light-custom">
                        <h5 class="mb-0">Shop Items</h5>
                    </div>
                    <div class="card-body">
                        <!-- Shop Item 1 -->
                        <div class="row align-items-center mb-4">
                            <div class="col-md-2">
                                <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" class="img-fluid rounded shadow" alt="Product">
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">Elegant Centerpieces</h5>
                                <p class="mb-1 text-muted">Set of 6 elegant centerpieces for wedding or formal events.</p>
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                    <span class="ms-1">4.5/5</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="updateQuantity(this, -1)">-</button>
                                    <input type="text" class="form-control text-center" value="1" readonly>
                                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="updateQuantity(this, 1)">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <h5 class="mb-0">&#8369;9999.99</h5>
                                <button class="btn btn-sm btn-link text-danger p-0" onclick="removeItem(this)">Remove</button>
                            </div>
                        </div>
                        <hr>

                        <!-- Shop Item 2 -->
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="https://images.unsplash.com/photo-1551135042-36d5e6965d6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" class="img-fluid rounded shadow" alt="Product">
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">Chiavari Chairs</h5>
                                <p class="mb-1 text-muted">Premium chiavari chairs for elegant seating at your event.</p>
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <span class="ms-1">5/5</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="updateQuantity(this, -1)">-</button>
                                    <input type="text" class="form-control text-center" value="1" readonly>
                                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="updateQuantity(this, 1)">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <h5 class="mb-0">&#8369;9999.99</h5>
                                <button class="btn btn-sm btn-link text-danger p-0" onclick="removeItem(this)">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="shop.html" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>Continue Shopping
                    </a>
                    <a href="checkout.html" class="btn btn-primary">
                        Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
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
                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery Fee</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 fw-bold fs-5">
                            <span>Total</span>
                            <span>&#8369;9999.99</span>
                        </div>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> Your order qualifies for free setup!
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Secure Payment</h6>
                        <div class="d-flex">
                            <i class="bi bi-shield-check display-6 text-success me-3"></i>
                            <p class="mb-0">Your payment information is processed securely. We do not store your credit card details.</p>
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