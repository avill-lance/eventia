<?php
    include __DIR__ . "/functions/session.php";
    include __DIR__ . "/functions/FunctionCompilation.php";
    checkToken();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Eventia</title>
    <link rel="icon" type="image/png" href="assets/Logo_BG.png">
    <link rel="stylesheet" href="css/bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verification-container {
            max-width: 500px;
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .otp-input {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 8px;
        }
        .resend-link {
            cursor: pointer;
        }
        .countdown {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- OTP Verification Container -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-lg verification-container">
                    <div class="card-body p-5">

                        <!-- Verification Icon -->
                        <div class="text-center mb-4">
                            <i class="bi bi-envelope-check display-1 text-primary"></i>
                        </div>
                        
                        <h3 class="text-center mb-3 section-title">Verify Your Email</h3>
                        
                        <p class="text-center text-muted mb-4">
                            We've sent a 4-digit verification code to<br>
                            <strong id="userEmailDisplay"><?php echo htmlspecialchars($_GET['email'] ?? ''); ?></strong>
                        </p>
                        
                        <!-- OTP Verification Form -->
                        <form id="otpVerificationForm" method="POST">
                            <input type="hidden" id="userEmail" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                            <input type="hidden" id="purpose" name="purpose" value="<?php echo htmlspecialchars($_GET['purpose'] ?? ''); ?>">
                            
                            <div class="mb-4">
                                <label for="otp" class="form-label">Enter Verification Code</label>
                                <input type="text" 
                                    class="form-control form-control-lg otp-input" 
                                    id="otp" 
                                    name="otp" 
                                    required 
                                    maxlength="4" 
                                    pattern="[0-9]{4}"
                                    placeholder="0000"
                                    autocomplete="one-time-code">
                                <div class="form-text">Enter the 4-digit code sent to your email</div>
                            </div>
                                                    
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="verifyBtn">
                                    <i class="bi bi-shield-check me-2"></i>Verify Account
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-2">
                                    Didn't receive the code? 
                                    <a href="#" class="resend-link text-decoration-none" id="resendOtp">
                                        <span id="resendText">Resend OTP</span>
                                    </a>
                                </p>
                                <div class="countdown" id="countdown"></div>
                            </div>
                        </form>
                        
                        <!-- Success/Error Message -->
                        <div id="message" class="mt-3"></div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="small mb-0">
                                Need help? <a href="contact.php" class="text-decoration-none">Contact Support</a><br>
                                <a href="registration.php" class="text-decoration-none small">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Registration
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.7.1.js"></script>
    <script src="js/js/bootstrap.bundle.min.js"></script>
    <script src="js/VerifyOTP.js"></script>
</body>
</html>