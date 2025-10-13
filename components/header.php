<?php
    // ### Establish Session ###
    include  __DIR__ . "/../functions/session.php";

    // ### Include functions compilation ###
    include  __DIR__ . "/../functions/FunctionCompilation.php";
    isLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventia</title>
    <link rel="icon" type="image/png" href="assets/Logo_BG.png">
    <link rel="stylesheet" href="css/bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/css/datatables.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/self_booking.css">
    
</head>
<body>

<!-- Load Bootstrap JS early for ALL pages -->
<script src="js/js/bootstrap.bundle.min.js"></script>

<!-- Navigation  -->
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
                    <a class="nav-link" href="packages.php">Booking</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="services.php">Services</a>
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
                <a class="nav-link p-0" id="userProfile" href="profile.php">
                    Welcome, <?php echo htmlspecialchars($_SESSION["first_name"] ?? 'User'); ?>
                </a>
            </div>
        </div>
    </div>
</nav>