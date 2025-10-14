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
    <div class="container d-flex vh-90 gap-4 align-items-center justify-content-center mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="section-title text-center">Change Password</h1>
            </div>
            <form method="POST" id="getEmail">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="newPassword" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <button class="btn btn-warning" name="sendOTP" id="sendOTP" type="submit">Send OTP</button>
                </div>
            </form>
        </div>
    </div>
    
<script src="js/sweetalert2@11.js"></script>
<script src="js/jquery-3.7.1.js"></script>
<script src="js/js/bootstrap.bundle.min.js"></script>
<script src="js/EditProfile.js"></script>
<script>

$("#getEmail").submit(function(e){
    e.preventDefault();
  Swal.fire({
    title: "OTP",
    text: "You will receive an OTP to your associated email",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes",
    cancelButtonText: "No"
  }).then((result) => {
    if (result.isConfirmed){
      // Sends otp to user
        getOTP();
    }
  });
})

function getOTP(){
    // Get email from login form
    const email = $('#email').val();
    
    if(!email) {
        Swal.fire('Error!', 'Email is required to send OTP.', 'error');
        return;
    }
    
    $.ajax({
        url: 'functions/SendOTP.php',
        type: 'POST',
        data: { email: email,
            purpose: 'forgot'
        },
        success: function(response) {
            if (response.trim() === 'sent') {
                Swal.fire({
                    title: 'OTP Sent!',
                    text: 'Verification code sent to your email. Please check your inbox.',
                    icon: 'success',
                    confirmButtonText: 'Proceed to Verification'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let params = new URLSearchParams();
                        params.append('email', email);
                        params.append('purpose', 'changepassword');  // Make sure this is added
                        
                        console.log("Redirecting with params:", params.toString());
                        window.location.href = 'verifyingemail.php?' + params.toString();
                    }
                });
            } else if (response.trim() === 'invalid_email') {
                Swal.fire('Error!', 'Invalid email address.', 'error');
            } else if (response.trim() === 'mail_error') {
                Swal.fire('Error!', 'Failed to send OTP. Please try again.', 'error');
            } else if (response.trim() === 'database_error') {
                Swal.fire('Error!', 'Database error. Please contact support.', 'error');
            } else {
                Swal.fire('Error!', 'Failed to send OTP: ' + response, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error: ' + error);
            Swal.fire('Error!', 'Network error. Please check your connection.', 'error');
        }
    });
}
</script>
</body>
</html>
