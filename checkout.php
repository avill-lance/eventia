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
    <title>Checkout - Eventia</title>
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
                    <a href="cart.php" class="cart-btn-v3 me-3">
                        <i class="bi bi-cart3 cart-icon"></i>
                        <span class="badge">3</span>
                    </a>
                    <a class="nav-link p-0" id="userProfile" href="profile.php">Welcome, <?php echo $_SESSION["first_name"]; ?></a>
                </div>
            </div>
        </div>
    </nav>

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
                            <span>&#8369;9999.99</span>
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
                <div class="card mb-4">
                    <div class="card-header bg-light-custom">
                        <h5 class="mb-0">Event Details</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="eventType" class="form-label">Event Type</label>
                                    <select class="form-select" id="eventType" required>
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
                                    <input type="date" class="form-control" id="eventDate" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="guestCount" class="form-label">Number of Guests</label>
                                    <input type="number" class="form-control" id="guestCount" min="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="eventLocation" class="form-label">Event Location</label>
                                    <input type="text" class="form-control" id="eventLocation" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="specialRequests" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="specialRequests" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light-custom">
                        <h5 class="mb-0">Billing Information</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zip" class="form-label">ZIP</label>
                                    <input type="text" class="form-control" id="zip" required>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light-custom">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="creditCard" checked>
                                <label class="form-check-label" for="creditCard">
                                    Credit Card
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="paypal">
                                <label class="form-check-label" for="paypal">
                                    PayPal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="bankTransfer">
                                <label class="form-check-label" for="bankTransfer">
                                    Bank Transfer
                                </label>
                            </div>
                        </div>

                        <div id="creditCardForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cardNumber" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cardName" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="cardName" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="expiry" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123" required>
                                </div>
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
                            <button type="submit" class="btn btn-primary btn-lg">Complete Booking</button>
                        </div>
                        <p class="text-center mt-3 mb-0">
                            <small>Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.</small>
                        </p>
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