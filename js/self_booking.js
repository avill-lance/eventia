window.nextStep = nextStep;
window.prevStep = prevStep;
window.selectVenueType = selectVenueType;
window.selectVenue = selectVenue;
window.showCustomization = showCustomization;
window.saveCustomization = saveCustomization;

console.log('✅ All functions exposed to global scope');
console.log('nextStep available:', typeof window.nextStep);
console.log('prevStep available:', typeof window.prevStep);

// Service customization data
let serviceCustomizations = {};
let currentCustomizingService = null;

// Generate booking reference on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Initializing self-booking page...');
    console.log('✅ Bootstrap available:', typeof bootstrap !== 'undefined');
    
    generateBookingReference();
    updateOrderSummary();
    updateTotalAmount();
    
    // Add event listeners for dynamic updates
    document.querySelectorAll('input[name="package"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateOrderSummary();
            updateTotalAmount();
        });
    });
    
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateOrderSummary();
            updateTotalAmount();
        });
    });
    
    // Event delegation for customization buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('customize-service-btn')) {
            const serviceId = e.target.getAttribute('data-service-id');
            const serviceName = e.target.getAttribute('data-service-name');
            const isCustomizable = e.target.getAttribute('data-customizable') === 'true';
            console.log('🎯 Customization button clicked:', { serviceId, serviceName, isCustomizable });
            showCustomization(serviceId, serviceName, isCustomizable);
        }
    });
    
    // Event listener for save customization button
    document.getElementById('saveCustomizationBtn').addEventListener('click', saveCustomization);
});

// [Keep all the other functions exactly the same - calculateTotalAmount, updateTotalAmount, generateBookingReference, etc.]
// Function to calculate total amount
function calculateTotalAmount() {
    let total = 0;
    
    // Package price
    const selectedPackage = document.querySelector('input[name="package"]:checked');
    if(selectedPackage) {
        total += parseFloat(selectedPackage.getAttribute('data-price')) || 0;
    }
    
    // Services prices
    document.querySelectorAll('.service-checkbox:checked').forEach(service => {
        total += parseFloat(service.getAttribute('data-price')) || 0;
    });
    
    return total;
}

// Function to update total amount display
function updateTotalAmount() {
    const total = calculateTotalAmount();
    document.getElementById('amount').textContent = 'Total: ₱' + total.toLocaleString(undefined, { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}

function generateBookingReference() {
    const timestamp = Date.now().toString();
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    const reference = 'EVT-' + timestamp.slice(-6) + random;
    document.getElementById('bookingReference').value = reference;
}

function selectVenueType(type) {
    document.getElementById('venueType').value = type;
    
    if (type === 'rental') {
        document.getElementById('venueSelection').style.display = 'block';
        document.getElementById('ownVenueForm').style.display = 'none';
        loadVenues();
    } else {
        document.getElementById('venueSelection').style.display = 'none';
        document.getElementById('ownVenueForm').style.display = 'block';
        document.getElementById('venueId').value = '';
        document.getElementById('eventLocation').value = '';
        document.getElementById('fullAddress').value = '';
    }
}

function loadVenues() {
    fetch('get_venues.php')
        .then(response => response.json())
        .then(venues => {
            const container = document.getElementById('venuesContainer');
            container.innerHTML = '';
            
            venues.forEach(venue => {
                const venueCard = `
                    <div class="col-md-6 mb-3">
                        <div class="card venue-card">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selected_venue" value="${venue.venue_id}" 
                                           data-name="${venue.venue_name}" data-location="${venue.location}" 
                                           data-description="${venue.description}" onchange="selectVenue(this)">
                                    <label class="form-check-label w-100">
                                        <h6>${venue.venue_name}</h6>
                                        <p class="text-muted mb-1">${venue.venue_type}</p>
                                        <p class="text-muted mb-1">Capacity: ${venue.capacity} people</p>
                                        <p class="text-muted mb-1">${venue.location}</p>
                                        <p class="text-primary fw-bold">₱${parseFloat(venue.price).toLocaleString()}</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += venueCard;
            });
        })
        .catch(error => {
            console.error('Error loading venues:', error);
            document.getElementById('venuesContainer').innerHTML = 
                '<div class="col-12 text-center text-muted">Unable to load venues. Please try again.</div>';
        });
}

function selectVenue(element) {
    const venueId = element.value;
    const venueName = element.getAttribute('data-name');
    const venueLocation = element.getAttribute('data-location');
    const venueDescription = element.getAttribute('data-description');
    
    document.getElementById('venueId').value = venueId;
    document.getElementById('eventLocation').value = venueName + ', ' + venueLocation;
    document.getElementById('fullAddress').value = venueDescription || venueLocation;
}

// Customization function - NOW WORKING PROPERLY
function showCustomization(serviceId, serviceName, isCustomizable) {
    console.log('🔄 Showing customization modal for service:', serviceId);
    currentCustomizingService = serviceId;
    
    const modalTitle = document.getElementById('customizationModalTitle');
    const modalBody = document.getElementById('customizationModalBody');
    
    modalTitle.textContent = 'Customize: ' + serviceName;
    
    if (isCustomizable) {
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Service Package</label>
                        <select class="form-select" id="customPackage">
                            <option value="basic">Basic Package</option>
                            <option value="standard">Standard Package</option>
                            <option value="premium">Premium Package</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Number of Hours/Units</label>
                        <input type="number" class="form-control" id="customUnits" min="1" max="12" value="4">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Special Requirements</label>
                <textarea class="form-control" id="customRequirements" rows="3" placeholder="Any specific requirements or preferences..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Additional Notes</label>
                <input type="text" class="form-control" id="customNotes" placeholder="Any additional notes...">
            </div>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Your customization requests will be reviewed and confirmed by our team. Additional charges may apply.
            </div>
        `;
    } else {
        modalBody.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Special Instructions</label>
                <textarea class="form-control" id="customRequirements" rows="4" placeholder="Enter any special instructions or preferences for this service..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Additional Notes</label>
                <input type="text" class="form-control" id="customNotes" placeholder="Any additional notes...">
            </div>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Your requests will be forwarded to our service team for confirmation.
            </div>
        `;
    }
    
    // Load existing customization if any
    if (serviceCustomizations[serviceId]) {
        if (isCustomizable) {
            document.getElementById('customPackage').value = serviceCustomizations[serviceId].package || 'basic';
            document.getElementById('customUnits').value = serviceCustomizations[serviceId].units || 4;
        }
        document.getElementById('customRequirements').value = serviceCustomizations[serviceId].requirements || '';
        document.getElementById('customNotes').value = serviceCustomizations[serviceId].notes || '';
    }
    
    // Initialize Bootstrap modal - NOW WORKING!
    const modalElement = document.getElementById('customizationModal');
    const customizationModal = new bootstrap.Modal(modalElement);
    customizationModal.show();
    console.log('✅ Customization modal shown successfully');
}

function saveCustomization() {
    if (!currentCustomizingService) {
        console.error('❌ No service selected for customization');
        return;
    }
    
    console.log('💾 Saving customization for service:', currentCustomizingService);
    
    const customization = {
        package: document.getElementById('customPackage') ? document.getElementById('customPackage').value : null,
        units: document.getElementById('customUnits') ? parseInt(document.getElementById('customUnits').value) : null,
        requirements: document.getElementById('customRequirements').value,
        notes: document.getElementById('customNotes').value,
        timestamp: new Date().toISOString()
    };
    
    serviceCustomizations[currentCustomizingService] = customization;
    
    // Add hidden input for customization data
    let existingInput = document.querySelector(`input[name="customization[${currentCustomizingService}]"]`);
    if (!existingInput) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `customization[${currentCustomizingService}]`;
        input.value = JSON.stringify(customization);
        document.getElementById('bookingForm').appendChild(input);
    } else {
        existingInput.value = JSON.stringify(customization);
    }
    
    // Hide modal
    const customizationModal = bootstrap.Modal.getInstance(document.getElementById('customizationModal'));
    customizationModal.hide();
    
    // Show success feedback
    const customizeBtn = document.querySelector(`[data-service-id="${currentCustomizingService}"]`);
    if (customizeBtn) {
        customizeBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Customized';
        customizeBtn.classList.remove('btn-outline-primary');
        customizeBtn.classList.add('btn-success');
    }
    
    console.log('✅ Customization saved successfully');
    currentCustomizingService = null;
}

function nextStep(step) {
    if (!validateStep(step - 1)) {
        return;
    }
    
    document.querySelectorAll('.step').forEach(stepEl => {
        stepEl.classList.remove('active');
    });
    
    document.getElementById('step-' + step).classList.add('active');
    
    const progress = (step - 1) * 25;
    document.getElementById('progress-bar').style.width = progress + '%';
    
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        if (index < step) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
    
    if (step === 5) {
        updateOrderSummary();
        updateTotalAmount();
    }
}

function prevStep(step) {
    document.querySelectorAll('.step').forEach(stepEl => {
        stepEl.classList.remove('active');
    });
    
    document.getElementById('step-' + step).classList.add('active');
    
    const progress = (step - 1) * 25;
    document.getElementById('progress-bar').style.width = progress + '%';
    
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        if (index < step) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
}

function validateStep(step) {
    const currentStep = document.getElementById('step-' + step);
    
    if (step === 1) {
        const packageSelected = document.querySelector('input[name="package"]:checked');
        if (!packageSelected) {
            alert('Please select an event package to continue.');
            return false;
        }
    }
    
    if (step === 2) {
        const venueType = document.getElementById('venueType').value;
        if (!venueType) {
            alert('Please select a venue option to continue.');
            return false;
        }
        
        if (venueType === 'rental') {
            const venueSelected = document.querySelector('input[name="selected_venue"]:checked');
            if (!venueSelected) {
                alert('Please select a venue to continue.');
                return false;
            }
        } else if (venueType === 'own') {
            const venueAddress = document.getElementById('venue_address').value;
            const venueCity = document.getElementById('venue_city').value;
            const venuePostal = document.getElementById('venue_postal').value;
            
            if (!venueAddress || !venueCity || !venuePostal) {
                alert('Please complete all venue address fields to continue.');
                return false;
            }
            
            document.getElementById('eventLocation').value = venueAddress;
            document.getElementById('fullAddress').value = `${venueAddress}, ${venueCity}, ${venuePostal}`;
        }
    }
    
    if (step === 4) {
        const requiredFields = ['contact_name', 'contact_email', 'contact_phone', 'event_date', 'event_time'];
        for (let field of requiredFields) {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                alert('Please complete all required fields to continue.');
                element.focus();
                return false;
            }
        }
        
        const email = document.getElementById('contact_email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address.');
            document.getElementById('contact_email').focus();
            return false;
        }
        
        const eventDate = new Date(document.getElementById('event_date').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (eventDate < today) {
            alert('Event date cannot be in the past.');
            document.getElementById('event_date').focus();
            return false;
        }
    }
    
    return true;
}

function updateOrderSummary() {
    const summaryContainer = document.getElementById('orderSummary');
    let total = 0;
    let summaryHTML = '';
    
    // Package
    const selectedPackage = document.querySelector('input[name="package"]:checked');
    if (selectedPackage) {
        const packagePrice = parseFloat(selectedPackage.getAttribute('data-price'));
        total += packagePrice;
        summaryHTML += `
            <tr>
                <td><strong>Package</strong></td>
                <td>${selectedPackage.value}</td>
                <td class="text-end">₱${packagePrice.toLocaleString()}</td>
            </tr>
        `;
    }
    
    // Services
    const selectedServices = document.querySelectorAll('.service-checkbox:checked');
    selectedServices.forEach(service => {
        const serviceName = service.value;
        const servicePrice = parseFloat(service.getAttribute('data-price')) || 0;
        total += servicePrice;
        summaryHTML += `
            <tr>
                <td><strong>Service</strong></td>
                <td>${serviceName}</td>
                <td class="text-end">₱${servicePrice.toLocaleString()}</td>
            </tr>
        `;
    });
    
    summaryContainer.innerHTML = summaryHTML || '<tr><td colspan="3" class="text-center text-muted">No items selected</td></tr>';
    document.getElementById('amount').textContent = `Total: ₱${total.toLocaleString()}`;
}

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

// Calendar functionality
let currentDate = new Date();
let selectedCalendarDate = null;

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for calendar button
    document.getElementById('openCalendar').addEventListener('click', openCalendar);
    
    // Calendar navigation
    document.getElementById('prevMonth').addEventListener('click', previousMonth);
    document.getElementById('nextMonth').addEventListener('click', nextMonth);
    
    // Confirm date selection
    document.getElementById('confirmDate').addEventListener('click', confirmDateSelection);
    
    // Initialize calendar
    generateCalendar(currentDate);
});

function openCalendar() {
    console.log('📅 Opening calendar modal');
    const calendarModal = new bootstrap.Modal(document.getElementById('calendarModal'));
    calendarModal.show();
    
    // Generate calendar for current month
    generateCalendar(currentDate);
}

function generateCalendar(date) {
    const calendarDays = document.getElementById('calendarDays');
    const currentMonthYear = document.getElementById('currentMonthYear');
    
    // Set current month year display
    currentMonthYear.textContent = date.toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
    });
    
    // Clear previous calendar
    calendarDays.innerHTML = '';
    
    // Get first day of month and total days
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    const totalDays = lastDay.getDate();
    const startingDay = firstDay.getDay(); // 0 = Sunday
    
    // Add empty cells for days before the first day of month
    for (let i = 0; i < startingDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day other-month';
        emptyDay.textContent = '';
        calendarDays.appendChild(emptyDay);
    }
    
    // Add days of the month
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let day = 1; day <= totalDays; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = day;
        
        const currentDate = new Date(date.getFullYear(), date.getMonth(), day);
        
        // Check if it's today
        if (currentDate.getTime() === today.getTime()) {
            dayElement.classList.add('today');
        }
        
        // Check if it's in the past
        if (currentDate < today) {
            dayElement.classList.add('unavailable');
            dayElement.title = 'Date has passed';
            console.log(`❌ ${currentDate.toDateString()} - Past date`);
        } else {
            // Use the checkAndSetAvailability function
            checkAndSetAvailability(currentDate, dayElement);
        }
        
        calendarDays.appendChild(dayElement);
    }
}

// Make sure this function is properly defined
async function checkAndSetAvailability(date, element) {
    try {
        const availability = await getDateAvailability(date);
        console.log(`📅 ${date.toDateString()} - Status: ${availability.status}, Message: ${availability.message}`);
        
        // Clear any existing availability classes
        element.classList.remove('available', 'partially-available', 'unavailable');
        
        // Add the correct availability class
        element.classList.add(availability.status);
        element.title = availability.message;
        
        // Remove any existing click events
        element.replaceWith(element.cloneNode(true));
        const newElement = element.parentElement.lastElementChild;
        
        // Only add click event if the date is available or partially available
        if (availability.status !== 'unavailable') {
            newElement.addEventListener('click', () => {
                console.log(`🎯 Clicked on ${date.toDateString()} with status: ${availability.status}`);
                selectDate(date, newElement);
            });
            console.log(`✅ ${date.toDateString()} - Click event added`);
        } else {
            console.log(`🚫 ${date.toDateString()} - No click event added (unavailable)`);
        }
        
    } catch (error) {
        console.error('Error in checkAndSetAvailability:', error);
        // Fallback: mark as available and add click event
        element.classList.remove('available', 'partially-available', 'unavailable');
        element.classList.add('available');
        element.title = 'Available';
        element.addEventListener('click', () => selectDate(date, element));
    }
}

// Add this helper function for async availability checking
async function checkAndSetAvailability(date, element) {
    const availability = await getDateAvailability(date);
    element.classList.add(availability.status);
    element.title = availability.message;
    
    if (availability.status !== 'unavailable') {
        element.addEventListener('click', () => selectDate(date, element));
    }
}

async function getDateAvailability(date) {
    const dateStr = date.toISOString().split('T')[0];
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Check if date is in the past
    if (date < today) {
        return { status: 'unavailable', message: 'Date has passed' };
    }
    
    try {
        console.log('🔍 Checking availability for:', dateStr);
        
        // Get availability data from server
        const response = await fetch('get_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                month: date.getMonth() + 1,
                year: date.getFullYear()
            })
        });
        
        if (!response.ok) {
            // If the file doesn't exist, use fallback logic
            console.warn('⚠️ Availability endpoint not available, using fallback logic');
            return getFallbackAvailability(date);
        }
        
        const data = await response.json();
        
        if (data.error) {
            console.error('Error from server:', data.error);
            return getFallbackAvailability(date);
        }
        
        console.log('📊 Server response:', data);
        
        // Check if date is fully booked
        if (data.booked_dates && data.booked_dates.includes(dateStr)) {
            return { status: 'unavailable', message: 'Fully booked for this date' };
        }
        
        // Check if date has limited availability
        if (data.limited_dates && data.limited_dates.includes(dateStr)) {
            return { status: 'partially-available', message: 'Limited availability' };
        }
        
        // Weekend check
        const day = date.getDay();
        if (day === 0 || day === 6) { // Weekend
            return { status: 'partially-available', message: 'Weekend availability' };
        }
        
        return { status: 'available', message: 'Available' };
        
    } catch (error) {
        console.error('Error checking availability:', error);
        // Use fallback logic
        return getFallbackAvailability(date);
    }
}

// Fallback function when the PHP file is not available
function getFallbackAvailability(date) {
    const dateStr = date.toISOString().split('T')[0];
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Check if date is in the past
    if (date < today) {
        return { status: 'unavailable', message: 'Date has passed' };
    }
    
    // Simple fallback logic - mark some dates as unavailable for testing
    const unavailableDates = [
        '2025-10-15',
        '2025-10-16', 
        '2025-10-25'
    ];
    
    const limitedDates = [
        '2025-10-18',
        '2025-10-19',
        '2025-10-26'
    ];
    
    if (unavailableDates.includes(dateStr)) {
        return { status: 'unavailable', message: 'Fully booked (fallback)' };
    }
    
    if (limitedDates.includes(dateStr)) {
        return { status: 'partially-available', message: 'Limited availability (fallback)' };
    }
    
    // Weekend check
    const day = date.getDay();
    if (day === 0 || day === 6) { // Weekend
        return { status: 'partially-available', message: 'Weekend availability (fallback)' };
    }
    
    return { status: 'available', message: 'Available (fallback)' };
}

document.addEventListener('DOMContentLoaded', function() {
    const eventDateInput = document.getElementById('event_date');
    
    if (eventDateInput) {
        eventDateInput.addEventListener('change', async function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Please select a future date.');
                this.value = '';
                return;
            }
            
            // Check availability for manually entered date
            const availability = await getDateAvailability(selectedDate);
            
            if (availability.status === 'unavailable') {
                alert('The selected date is not available. Please choose another date or use the calendar to check availability.');
                this.value = '';
            } else if (availability.status === 'partially-available') {
                if (confirm('This date has limited availability. Would you like to proceed?')) {
                    // User confirmed, keep the date
                    console.log('User confirmed limited availability date:', this.value);
                } else {
                    this.value = '';
                }
            }
        });
    }
});

function selectDate(date, element) {
    console.log('🟢 selectDate called with:', date);
    
    // Debug the element state
    debugCalendarClick(date, element);
    
    // Check if the element is actually unavailable
    if (element.classList.contains('unavailable')) {
        console.log('❌ Element is marked as unavailable, ignoring click');
        return;
    }
    
    // Remove selected class from all days
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.classList.remove('selected');
    });
    
    // Add selected class to clicked day
    element.classList.add('selected');
    
    // Store selected date
    selectedCalendarDate = date;
    
    // Update selected date info
    updateSelectedDateInfo(date);
    
    console.log('✅ Date selected successfully:', date);
}

function updateSelectedDateInfo(date) {
    const selectedDateInfo = document.getElementById('selectedDateInfo');
    const selectedDateAvailability = document.getElementById('selectedDateAvailability');
    
    const availability = getDateAvailability(date);
    
    selectedDateInfo.innerHTML = `
        <strong>${date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        })}</strong>
    `;
    
    let availabilityBadge = '';
    if (availability.status === 'available') {
        availabilityBadge = '<span class="badge bg-success">Available</span>';
    } else if (availability.status === 'partially-available') {
        availabilityBadge = '<span class="badge bg-warning text-dark">Limited Availability</span>';
    } else {
        availabilityBadge = '<span class="badge bg-danger">Unavailable</span>';
    }
    
    selectedDateAvailability.innerHTML = `
        ${availabilityBadge}
        <div class="mt-1"><small>${availability.message}</small></div>
    `;
}

function confirmDateSelection() {
    if (!selectedCalendarDate) {
        alert('Please select a date from the calendar.');
        return;
    }
    
    const availability = getDateAvailability(selectedCalendarDate);
    if (availability.status === 'unavailable') {
        alert('The selected date is not available. Please choose another date.');
        return;
    }
    
    // Format date for input field (YYYY-MM-DD)
    const formattedDate = selectedCalendarDate.toISOString().split('T')[0];
    
    // Set the date in the form field
    document.getElementById('event_date').value = formattedDate;
    
    // Close the modal
    const calendarModal = bootstrap.Modal.getInstance(document.getElementById('calendarModal'));
    calendarModal.hide();
    
    console.log('✅ Date selected:', formattedDate);
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar(currentDate);
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar(currentDate);
}

// Add to global scope
window.openCalendar = openCalendar;
