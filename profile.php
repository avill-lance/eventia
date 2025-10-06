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
                <div>
                    <a href="cart.php" class="cart-btn-v3">
                        <i class="bi bi-cart3 cart-icon"></i>
                        <span class="badge">3</span>
                    </a>
                </div>
                <div>
                    <a class="mx-2 nav-link active" id="userProfile" href="profile.php">Welcome, <?php echo $_SESSION["first_name"]; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container d-flex vh-90 gap-4 align-items-center justify-content-center mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="section-title text-center">Profile Information</h1>
            </div>
            <form method="POST">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" value="<?php echo $rows["first_name"]; ?>" class="form-control editing" id="firstname" name="firstname" readonly>
                        </div>
                        <div class="col">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" value="<?php echo $rows["last_name"]; ?>" class="form-control editing" id="lastname" name="lastname" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="text" value="<?php echo $rows["email"]; ?>" class="form-control editing" id="email" name="email" readonly>
                        </div>
                        <div class="col">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="number" value="<?php echo $rows["phone"]; ?>" class="form-control editing" id="phone" name="phone" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="zip" class="form-label">Zip Code</label>
                            <input type="text" value="<?php echo $rows["zip"]; ?>" class="form-control editing" id="zip" name="zip" readonly>
                        </div>
                        <div class="col">
                            <label for="city" class="form-label">City</label>
                            <input type="text" value="<?php echo $rows["city"]; ?>" class="form-control editing" id="city" name="city" readonly>
                        </div>
                    </div>
                    <div class="row-12 mb-5">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" value="<?php echo $rows["address"]; ?>" class="form-control editing" id="address" name="address" readonly>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button class="btn btn-secondary w-100" type="button" id="cancelBtn">Cancel Edit</button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success w-100" type="submit" id="passBtn" name="passBtn">Confirm Edit</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button class="btn btn-secondary w-100" type="button" id="editBtn" name="editBtn">Edit Profile</button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-danger w-100" type="button" id="logoutBtn" name="logoutBtn">Logout</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

<script src="js/jquery-3.7.1.js"></script>
<script src="js/sweetalert2@11.js"></script>
<script src="js/EditProfile.js"></script>
<script src="js/js/bootstrap.bundle.min.js"></script>
</body>
</html>