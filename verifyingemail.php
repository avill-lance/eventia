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
    <script>
        $(document).ready(function() {
            let canResend = false;
            let countdownTime = 60; // 60 seconds countdown
            
            // Start countdown timer
            startCountdown();
            
            // OTP input formatting
            $('#otp').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 4) {
                    $('#verifyBtn').focus();
                }
            });
            
            // Form submission
            $('#otpVerificationForm').on('submit', function(e) {
                e.preventDefault();
                
                const otp = $('#otp').val();
                const email = $('#userEmail').val();
                
                if (otp.length !== 4) {
                    showMessage('Please enter a valid 4-digit OTP code.', 'danger');
                    return;
                }
                
                // Disable button and show loading
                $('#verifyBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Verifying...');
                
                $.ajax({
                    url: 'functions/VerifyOTP.php',
                    type: 'POST',
                    data: {
                        email: email,
                        otp: otp
                    },
                    // In your AJAX success function, add more detailed logging:
                    success: function(response) {
                        console.log("OTP Verification Response:", response);
                        
                        if (response === 'verified') {
                            showMessage('Email verified successfully! Redirecting to login...', 'success');
                            setTimeout(() => {
                                window.location.href = 'login.php';
                            }, 2000);
                        } else if (response === 'invalid') {
                            showMessage('Invalid OTP code. Please try again.', 'danger');
                        } else if (response === 'expired') {
                            showMessage('OTP has expired. Please request a new one.', 'warning');
                        } else if (response === 'error') {
                            showMessage('System error. Please try again or contact support.', 'danger');
                        } else {
                            showMessage('Unexpected response: ' + response, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error Details:");
                        console.log("Status:", status);
                        console.log("Error:", error);
                        console.log("Response Text:", xhr.responseText);
                        console.log("Ready State:", xhr.readyState);
                        console.log("Status Code:", xhr.status);
                        
                        if (xhr.status === 404) {
                            showMessage('Verification service not found. Please contact support.', 'danger');
                        } else if (xhr.status === 500) {
                            showMessage('Server error. Please try again later.', 'danger');
                        } else if (xhr.status === 0) {
                            showMessage('Network connection failed. Please check your internet connection.', 'danger');
                        } else {
                            showMessage('Verification failed. Error: ' + error, 'danger');
                        }
                    },
                    complete: function() {
                        $('#verifyBtn').prop('disabled', false).html('<i class="bi bi-shield-check me-2"></i>Verify Account');
                    }
                });
            });
            
            // Resend OTP functionality
            $('#resendOtp').on('click', function(e) {
                e.preventDefault();
                
                if (!canResend) return;
                
                const email = $('#userEmail').val();
                
                $.ajax({
                    url: 'ResendOTP.php',
                    type: 'POST',
                    data: { email: email },
                    success: function(response) {
                        if (response === 'resent') {
                            showMessage('New OTP sent to your email!', 'success');
                            startCountdown();
                        } else {
                            showMessage('Failed to resend OTP. Please try again.', 'danger');
                        }
                    },
                    error: function() {
                        showMessage('Network error. Please try again.', 'danger');
                    }
                });
            });
            
            function startCountdown() {
                canResend = false;
                let timeLeft = countdownTime;
                
                $('#resendOtp').addClass('text-muted');
                $('#resendText').text('Resend OTP');
                
                const countdownInterval = setInterval(() => {
                    $('#countdown').text(`Resend available in ${timeLeft} seconds`);
                    timeLeft--;
                    
                    if (timeLeft < 0) {
                        clearInterval(countdownInterval);
                        $('#countdown').text('');
                        $('#resendOtp').removeClass('text-muted');
                        $('#resendText').text('Resend OTP');
                        canResend = true;
                    }
                }, 1000);
            }
            
            function showMessage(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 
                                 type === 'danger' ? 'alert-danger' : 
                                 type === 'warning' ? 'alert-warning' : 'alert-info';
                
                $('#message').html(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                
                // Auto-hide success messages after 5 seconds
                if (type === 'success') {
                    setTimeout(() => {
                        $('#message .alert').alert('close');
                    }, 5000);
                }
            }
            
            // Auto-focus OTP input
            $('#otp').focus();
        });
    </script>
</body>
</html>