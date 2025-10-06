<?php 
    //Establish Session
    include __DIR__ . "/functions/session.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eventia</title>
    <link rel="icon" type="image/png" href="assets/Logo_BG.png">
    <link rel="stylesheet" href="css/bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Login Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <form id="loginForm" method="POST">
                            <div class="text-center mb-4">
                                <img src="assets/EventiaLogo.png" alt="Eventia Logo" style="width: 70px; height: 70px;">
                            </div>
                            <div class="text-center mb-4">
                                <h2>Sign In</h2>
                                <p class="text-muted">Enter your credentials to access your account</p>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" placeholder="name@example.com" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" placeholder="Enter your password" name="password" required>
                                <div class="form-text">
                                    <a href="#forgotPassword" class="text-decoration-none">Forgot password?</a>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">
                                    Remember me
                                </label>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="login-btn">Sign In</button>
                            </div>
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? <a href="registration.php" class="text-decoration-none">Sign up</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<script src="js/jquery-3.7.1.js"></script>
<script src="js/js/bootstrap.bundle.min.js"></script>
<script src="js/AccountCreation.js"></script>
</body>
</html>