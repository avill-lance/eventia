//For registration function
$(document).ready(function(){
    $("#create-btn").click(function(e){
        e.preventDefault();
        $.ajax({
            url: 'functions/RegistrationProcess.php',
            method: 'POST',
            data: $("#registrationForm").serialize(),

            success: function(phpresponse){
                try {
                    // Try to parse JSON response (new format)
                    const response = JSON.parse(phpresponse);
                    
                    if(response.status === 'added'){
                        Swal.fire({
                            title: 'Registration Successful!',
                            text: 'Please verify your email to continue.',
                            icon: 'success',
                            confirmButtonText: 'Verify Email'
                        }).then((result) => {
                            if (result.isConfirmed) {

                                window.location.href = 'verifyingemail.php?email=' + encodeURIComponent(response.email);
                            }
                        });
                    }
                } catch (e) {
                    // Fallback to old response format
                    if(phpresponse.trim()==='added'){
                        // Get email from form
                        const email = $('#email').val();
                        Swal.fire({
                            title: 'Registration Successful!',
                            text: 'Please verify your email to continue.',
                            icon: 'success',
                            confirmButtonText: 'Verify Email'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'verifyingemail.php?email=' + encodeURIComponent(email);
                            }
                        });
                    }
                    else if(phpresponse.trim()==='existing'){
                        alert('Email has already been used');
                    }
                    else if(phpresponse.trim()==='required'){
                        alert('All fields are required!');
                    }
                    else if(phpresponse.trim()==='differentpassword'){
                        alert('Passwords does not match!');
                    }
                    else if(phpresponse.trim()==='error'){
                        alert('User was not added due to error...');
                    }
                    else{
                        alert('User was not added' + phpresponse);
                        console.log(phpresponse);
                    }
                }
            },
            error: function(xhr, status, error){
                alert("AJAX ERROR: " + error);
            }
        })
    })

    //For login function
    $('#login-btn').click(function(e){
        e.preventDefault();
        $.ajax({
            url: 'functions/LoginProcess.php',
            method: 'POST',
            data: $("#loginForm").serialize(),
            
            success: function(user){
                if(user.trim()==='empty'){
                    alert("Fields are required");
                }
                else if(user.trim()==='inactive'){
                    Swal.fire({
                    title: "Email has not been verified",
                    text: "Verify your email now?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ebcb3cff",
                    cancelButtonColor: "rgba(110, 110, 110, 1)",
                    confirmButtonText: "Yes"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        getOTP();
                    }
                    });
                }
                else if(user.trim()==='wrong'){
                    alert("Wrong email or password");
                }
                else if(user.trim()==='invalid'){
                    alert("Invalid email or password");
                }
                else if(user.trim()==='success'){
                    Swal.fire({
                    title: "Successful Login!",
                    icon: "success"});
                    setTimeout(() => {
                        window.location.href='index.php';
                    }, 2000);
                    
                }
                else{
                    alert("Error: " + user);
                }
            },
            error: function(xhr, status,error){
                alert("AJAX ERROR: " + error);
            }
        })
    })
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
        data: { email: email },
        success: function(response) {
            if (response.trim() === 'sent') {
                Swal.fire({
                    title: 'OTP Sent!',
                    text: 'Verification code sent to your email. Please check your inbox.',
                    icon: 'success',
                    confirmButtonText: 'Proceed to Verification'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'verifyingemail.php?email=' + encodeURIComponent(email);
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

// Multi-step form functionality
function nextStep(next) {
    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active');
    });
    document.getElementById('step' + next).classList.add('active');
    
    // Update progress bar
    const progressPercentage = (next / 2) * 100;
    document.querySelector('.progress-bar').style.width = progressPercentage + '%';
    document.querySelector('.progress-bar').textContent = 'Step ' + next + ' of 2';
}

function prevStep(prev) {
    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active');
    });
    document.getElementById('step' + prev).classList.add('active');
    
    // Update progress bar
    const progressPercentage = (prev / 2) * 100;
    document.querySelector('.progress-bar').style.width = progressPercentage + '%';
    document.querySelector('.progress-bar').textContent = 'Step ' + prev + ' of 2';
}