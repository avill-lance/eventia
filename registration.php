<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Eventia</title>
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
    <!-- Registration Container -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <!-- Progress Bar -->
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">Step 1 of 2</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Multi-step Form -->
                                 
                                <form id="registrationForm" method="POST">
                                    <!-- Step 1: Account Details -->
                                    <div class="step active" id="step1">
                                        <h4 class="mb-4 section-title">Account Information</h4>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="firstName" required placeholder="Juan"  name="firstname">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="lastName" required placeholder="Dela Cruz" name="lastname">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" required placeholder="juandelacruz@gmail.com" name="email">
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password" required name="password">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                                <input type="password" class="form-control" id="confirmPassword" required name="confirmPassword">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mt-4">
                                            <div></div> <!-- Empty div for spacing -->
                                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                        </div>
                                    </div>
                                    
                                    <!-- Step 2: Personal Details -->
                                    <div class="step" id="step2">
                                        <h4 class="mb-4 section-title">Personal Information</h4>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="number" class="form-control" id="phone" placeholder="09XX XXX XXXX" name="phone">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="address" placeholder="Complete Address" name="address">
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="city" class="form-label">City</label>
                                                <input type="text" class="form-control" id="city" placeholder="Tagaytay City" name="city">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="zip" class="form-label">ZIP Code</label>
                                                <input type="number" class="form-control" id="zip" placeholder="4120" name="zip">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mt-4">
                                            <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)"><i class="bi bi-arrow-left me-1"></i>Previous</button>
                                            <button type="submit" class="btn btn-primary" id="create-btn">Create Account <i class="bi bi-check-lg ms-1"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="bg-light-custom p-2 rounded mt-3">
                                    <h5 class="mb-4 section-title">Benefits of Registering</h5>
                                    
                                    <div class="benefit-item">
                                        <i class="bi bi-clock-history benefit-icon"></i>
                                        <div>
                                            <h6>Fast Booking</h6>
                                            <p class="small mb-0">Quick checkout with your saved information</p>
                                        </div>
                                    </div>
                                    
                                    <div class="benefit-item">
                                        <i class="bi bi-calendar-check benefit-icon"></i>
                                        <div>
                                            <h6>Manage Events</h6>
                                            <p class="small mb-0">Track all your events in one convenient place</p>
                                        </div>
                                    </div>
                                    
                                    <div class="benefit-item">
                                        <i class="bi bi-chat-dots benefit-icon"></i>
                                        <div>
                                            <h6>Priority Support</h6>
                                            <p class="small mb-0">Get faster responses from our event specialists</p>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <p class="small mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Sign in here</a></p>
                                </div>
                            </div>
                        </div>
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