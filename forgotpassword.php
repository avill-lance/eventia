<?php include __DIR__."/components/header.php"; ?>

    <div class="container d-flex vh-90 gap-4 align-items-center justify-content-center mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="section-title text-center">Change Password</h1>
            </div>
            <form method="POST" id="changepassword">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <a class="btn btn-secondary w-100" id="cancelChange" href="profile.php">Cancel</a>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success w-100" type="submit" id="savenewpass" name="savenewpass">Change</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__."/components/footer.php" ?>
<script>

$(document).ready(function(){
    $("#changepassword").submit(function(e){
        e.preventDefault();
        console.log("=== FORM SUBMISSION STARTED ===");
        console.log("Form data:", $("#changepassword").serialize());
        
        $.ajax({
            url: 'functions/UpdatePassword.php',
            type: 'POST',
            data: $("#changepassword").serialize(),
            
            success: function(phpresponse) {
                console.log("=== AJAX SUCCESS RESPONSE ===");
                console.log("Raw response:", phpresponse);
                console.log("Trimmed response:", phpresponse.trim());
                console.log("Response length:", phpresponse.length);
                
                const trimmed = phpresponse.trim();
                
                if(trimmed === 'empty'){
                    Swal.fire({
                        title: "Fields are required!",
                        icon: "warning",
                        confirmButtonText: "Okay",
                    });
                }
                else if(trimmed === 'different'){
                    Swal.fire({
                        title: "Passwords do not match!",
                        icon: "error",
                        confirmButtonText: "Okay",
                    });
                }
                else if(trimmed === 'updated'){
                    Swal.fire({
                        title: "Success!",
                        text: "Password has been updated!",
                        icon: "success",
                        confirmButtonText: "Okay",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'profile.php';
                        }
                    });
                }
                else if(trimmed === 'Error'){
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to update password. Please try again.",
                        icon: "error",
                        confirmButtonText: "Okay",
                    });
                }
                else {
                    Swal.fire({
                        title: "Unknown Response",
                        text: "Server returned: " + trimmed,
                        icon: "error",
                        confirmButtonText: "Okay",
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log("=== AJAX ERROR ===");
                console.log("Status:", status);
                console.log("Error:", error);
                console.log("Response Text:", xhr.responseText);
                
                Swal.fire({
                    title: "Network Error!",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    confirmButtonText: "Okay",
                });
            }
        });
    });
});    
</script>
<script>
console.log("=== DEBUG FORGOT PASSWORD ===");
console.log("EditProfile.js should be loaded");

// Check if jQuery is available
console.log("jQuery available:", typeof jQuery !== 'undefined');
if (typeof jQuery !== 'undefined') {
    console.log("jQuery version:", jQuery.fn.jquery);
}

// Check if our form exists
const form = document.getElementById('changepassword');
console.log("Form element found:", form);

// Check if our button exists  
const button = document.getElementById('savenewpass');
console.log("Button element found:", button);

// Test if our event handler code would work
if (typeof jQuery !== 'undefined' && form) {
    console.log("jQuery and form are available - event handler should work");
} else {
    console.log("PROBLEM: jQuery or form not available");
}
console.log("=== END DEBUG ===");
</script>