const firstname = document.getElementById("firstname");
const lastname = document.getElementById("lastname");
const email = document.getElementById("email");
const phone = document.getElementById("phone");
const zip = document.getElementById("zip");
const city = document.getElementById("city");
const address = document.getElementById("address");

$("#cancelBtn").hide();
$("#passBtn").hide();

// To store original values before editing
let originalValues = {};

editBtn.addEventListener("click", function () {
    // Save original values
    originalValues = {
        firstname: firstname.value,
        lastname: lastname.value,
        email: email.value,
        phone: phone.value,
        zip: zip.value,
        city: city.value,
        address: address.value
    };

    // Remove readonly
    [firstname, lastname, email, phone, zip, city, address].forEach(input => {
        input.removeAttribute("readonly");
    });

    // Toggle buttons
    $("#viewTransactions").hide();
    $("#editBtn").hide();
    $("#logoutBtn").hide();
    $("#changePassBtn").hide();
    $("#cancelBtn").css('display', 'block');
    $("#passBtn").css('display', 'block');
});

cancelBtn.addEventListener("click", function (e) {
    e.preventDefault();

    // Restore readonly
    [firstname, lastname, email, phone, zip, city, address].forEach(input => {
        input.setAttribute("readonly", true);
    });

    // Restore original values
    firstname.value = originalValues.firstname;
    lastname.value = originalValues.lastname;
    email.value = originalValues.email;
    phone.value = originalValues.phone;
    zip.value = originalValues.zip;
    city.value = originalValues.city;
    address.value = originalValues.address;

    // Toggle buttons back
    $("#viewTransactions").show();
    $("#editBtn").show();
    $("#logoutBtn").show();
    $("#changePassBtn").show();
    $("#cancelBtn").hide();
    $("#passBtn").hide();
});

document.getElementById("logoutBtn").addEventListener("click", function () {
  Swal.fire({
    title: "Are you sure?",
    text: "You will be logged out of your account.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, logout",
    cancelButtonText: "Stay"
  }).then((result) => {
    if (result.isConfirmed) {
      // Redirect to logout page
      window.location.href = "functions/LogoutFunction.php";
    }
  });
});
//Change password getting otp to confirm user
$("#changePassBtn").on("click", function(){
  Swal.fire({
    title: "Are you sure?",
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
            purpose: 'changepass'
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
