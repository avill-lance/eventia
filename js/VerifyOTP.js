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
        const purpose = $('#purpose').val();
        
        if (otp.length !== 4) {
            showMessage('Please enter a valid 4-digit OTP code.', 'danger');
            return;
        }
        
        // Disable button and show loading
        $('#verifyBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Verifying...');
        const formData = {
            email: $('#userEmail').val(),
            otp: $('#otp').val(),
            purpose: $('#purpose').val()
        };
    
        console.log("Form Data being sent:", formData);
    
        $.ajax({
            url: 'functions/VerifyOTP.php',
            type: 'POST',
            data: formData,

            // In your AJAX success function, add more detailed logging:
            success: function(response) {
                console.log("=== OTP VERIFICATION DEBUG ===");
                console.log("Raw Response:", JSON.stringify(response));
                console.log("Trimmed Response:", JSON.stringify(response.trim()));
                console.log("Response includes 'changepassword':", response.includes('changepassword'));
                console.log("Response === 'changepassword':", response.trim() === 'changepassword');
                console.log("Purpose from form:", $('#purpose').val());
                console.log("=== END DEBUG ===");
                
                const trimmedResponse = response.trim();
                
                if (trimmedResponse === 'verified') {
                    console.log("Going to login.php - verified without changepassword purpose");
                    showMessage('Email verified successfully! Redirecting to login...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                }
                else if (trimmedResponse === 'changepassword') {
                    console.log("Going to forgotpassword.php - changepassword purpose detected");
                    showMessage('Email verified successfully! Redirecting to password change...', 'success');
                    const userEmail = $('#userEmail').val();
                    setTimeout(() => {
                        window.location.href = 'forgotpassword.php?email=' + userEmail;
                    }, 2000);
                }
                else if (trimmedResponse === 'invalid') {
                    showMessage('Invalid OTP code. Please try again.', 'danger');
                } 
                else if (trimmedResponse === 'expired') {
                    showMessage('OTP has expired. Please request a new one.', 'warning');
                }
                else if (trimmedResponse === 'error') {
                    showMessage('System error. Please try again or contact support.', 'danger');
                } 
                else {
                    console.log("Unexpected response, defaulting to login");
                    showMessage('Unexpected response: ' + trimmedResponse + ' - Redirecting to login', 'warning');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
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