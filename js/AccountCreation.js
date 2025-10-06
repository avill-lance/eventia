//For registration function
$(document).ready(function(){
    $("#create-btn").click(function(e){
        e.preventDefault();
        $.ajax({
            url: 'functions/RegistrationProcess.php',
            method: 'POST',
            data: $("#registrationForm").serialize(),

            success: function(phpresponse){
                if(phpresponse.trim()==='added'){
                    alert('User has been added');
                    window.location.href='login.php';
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
            },
            error: function(xhr, status, error){
                alert("AJAX ERROR: " + error);
            }
        })
    })
})

//For login function
$(document).ready(function(){
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
                else if(user.trim()==='wrong'){
                    alert("Wrong email or password");
                }
                else if(user.trim()==='invalid'){
                    alert("Invalid email or password");
                }
                else if(user.trim()==='success'){
                    alert("Login Successful");
                    window.location.href='index.php';
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

// Form submission
// document.getElementById('registrationForm').addEventListener('submit', function(e) {
//     e.preventDefault();
//     alert('Registration successful! You can now log in to your account.');
//     window.location.href = 'login.html';
// });