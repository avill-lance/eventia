$(document).ready(function(){
$("#submitBooking").click(function(e){
    e.preventDefault();
    console.log("🔴 Submit button clicked!");
    
    // Validate form first
    if(!$("#bookingForm")[0].checkValidity()) {
        console.log("❌ Form validation failed");
        $("#bookingForm")[0].reportValidity();
        return;
    }
    console.log("✅ Form validation passed");
    
    // Check if terms are accepted
    if(!$("#terms").is(":checked")) {
        console.log("❌ Terms not accepted");
        Swal.fire('Error!', 'Please accept the terms and conditions to continue.', 'error');
        return;
    }
    console.log("✅ Terms accepted");
    
    // Show loading state
    const submitBtn = $('#submitBooking');
    const originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...').prop('disabled', true);
    console.log("🔄 Loading state activated");
    
    // Collect all form data properly
    const formData = new FormData();
    
    // Add basic form data
    const formInputs = $("#bookingForm").serializeArray();
    console.log("📝 Form inputs:", formInputs);
    formInputs.forEach(function(input) {
        formData.append(input.name, input.value);
    });
    
    // Add calculated amount
    const totalAmount = calculateTotalAmount();
    console.log("💰 Calculated amount:", totalAmount);
    formData.append('amount', totalAmount);
    
    // Add event type (from package)
    const selectedPackage = $('input[name="package"]:checked');
    if(selectedPackage.length) {
        formData.append('eventType', selectedPackage.val());
        console.log("🎯 Event type:", selectedPackage.val());
    }
    
    // Add customer info
    const contactName = $('#contact_name').val();
    formData.append('firstName', contactName.split(' ')[0]);
    formData.append('lastName', contactName.split(' ').slice(1).join(' '));
    formData.append('email', $('#contact_email').val());
    formData.append('phone', $('#contact_phone').val());
    
    console.log("👤 Customer info:", {
        firstName: contactName.split(' ')[0],
        lastName: contactName.split(' ').slice(1).join(' '),
        email: $('#contact_email').val(),
        phone: $('#contact_phone').val()
    });
    
    // For testing - uncomment this line to use test mode
    formData.append('test_mode', 'true');
    console.log("🧪 Test mode enabled");
    
    console.log("📤 Sending AJAX request...");
    
    $.ajax({
        url: "paymongo-payment-method/create_payment.php",
        method: "POST",
        dataType: "json",
        data: formData,
        processData: false, // Important for FormData
        contentType: false, // Important for FormData
        success: function(response){
            console.log("✅ AJAX Success - Full response:", response);
            submitBtn.html(originalText).prop('disabled', false);
            
            if(response.success && response.checkout_url) {
                console.log("🎉 Payment created successfully");
                // Store reference in sessionStorage for verification
                sessionStorage.setItem('paymentRef', response.reference);
                if(response.test_mode) {
                    sessionStorage.setItem('testMode', 'true');
                    console.log("🧪 Test payment created - Reference:", response.reference);
                    window.location.href = response.checkout_url;
                } else {
                    console.log("💳 Real payment created - Reference:", response.reference);
                    
                    // Show instructions for real payment
                    Swal.fire({
                        title: 'Redirecting to PayMongo',
                        html: 'You will be redirected to PayMongo to complete your payment.<br><br>' +
                              '<strong>Reference Number:</strong> ' + response.reference + '<br><br>' +
                              'Please complete the payment process and return to this site.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Proceed to PayMongo',
                        cancelButtonText: 'Stay Here',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log("🔄 Redirecting to:", response.checkout_url);
                            window.location.href = response.checkout_url;
                        } else {
                            console.log("🚫 User cancelled redirect");
                        }
                    });
                }
            } else {
                console.log("❌ Payment creation failed:", response);
                Swal.fire('Error!', response.error || 'Failed to create payment', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', error);
            console.log('Status:', xhr.status);
            console.log('Response Text:', xhr.responseText);
            submitBtn.html(originalText).prop('disabled', false);
            
            try {
                var errorResponse = JSON.parse(xhr.responseText);
                console.log('Error details:', errorResponse);
                Swal.fire('Error!', errorResponse.error || 'Network error: ' + error, 'error');
            } catch(e) {
                Swal.fire('Error!', 'Server error: ' + xhr.responseText, 'error');
            }
        }
    });
});

// Function to calculate total amount
function calculateTotalAmount() {
    let total = 0;
    
    // Package price
    const selectedPackage = $('input[name="package"]:checked');
    if(selectedPackage.length) {
        total += parseFloat(selectedPackage.data('price')) || 0;
    }
    
    // Services prices
    $('.service-checkbox:checked').each(function() {
        total += parseFloat($(this).data('price')) || 0;
    });
    
    console.log("🧮 Total calculated:", total);
    return total;
}
});
