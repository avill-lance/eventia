<?php 
session_start();
include __DIR__."/components/header.php"; 
require_once __DIR__ . '/database/config.php';

// Fetch packages from database
$packages = [];
$packages_result = $conn->query("SELECT package_id, package_name, package_description, base_price, event_type FROM tbl_packages WHERE status = 'active'");
while ($row = $packages_result->fetch_assoc()) {
    $packages[] = $row;
}

// Fetch services from database with details and features
$services = [];
$services_result = $conn->query("
    SELECT s.service_id, s.service_name, s.service_description, s.base_price, s.category, s.customizable, s.customization_options
    FROM tbl_services s 
    WHERE s.status = 'active'
");
while ($row = $services_result->fetch_assoc()) {
    // Get service details
    $details = [];
    $details_result = $conn->query("SELECT detail_name, price_min, price_max FROM tbl_service_details WHERE service_id = " . $row['service_id']);
    while ($detail = $details_result->fetch_assoc()) {
        $details[] = $detail;
    }
    $row['details'] = $details;
    
    // Get service features
    $features = [];
    $features_result = $conn->query("SELECT feature_name FROM tbl_service_features WHERE service_id = " . $row['service_id']);
    while ($feature = $features_result->fetch_assoc()) {
        $features[] = $feature['feature_name'];
    }
    $row['features'] = $features;
    
    $services[] = $row;
}

// Prepare packages data for JavaScript
$packages_js = [];
foreach ($packages as $pkg) {
    $packages_js[] = [
        'text' => $pkg['package_name'] . ' - ₱' . number_format($pkg['base_price'], 2),
        'value' => $pkg['package_name'],
        'price' => $pkg['base_price'],
        'id' => $pkg['package_id']
    ];
}

// Prepare services data for JavaScript
$services_js = [];
foreach ($services as $service) {
    $services_js[] = [
        'text' => $service['service_name'] . ' - ₱' . number_format($service['base_price'], 2),
        'value' => $service['service_id'],
        'name' => $service['service_name'],
        'price' => $service['base_price'],
        'customizable' => $service['customizable']
    ];
}
?>
<link rel="stylesheet" href="css/booking-improvements.css">


<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Guided Booking</h1>
        <p class="lead mb-4">Let our expert guide you through your perfect event</p>
    </div>
</div>

<!-- Guided Booking Interface -->
<div class="container my-5">
    <div class="row">
        
        <!-- Chat Area (Left Side) -->
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="service-icon me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-headset" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Event Planning Assistant</h5>
                            <small class="text-success"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Online</small>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="chatContainer" style="height: 500px; overflow-y: auto; background-color: #f8f9fa;">
                    <div id="chatMessages">
                        <!-- Initial message -->
                        <div class="chat-message admin-message mb-3">
                            <div class="d-flex align-items-start">
                                <div class="chat-avatar me-2">
                                    <div class="step-number" style="width: 35px; height: 35px; font-size: 0.9rem;">A</div>
                                </div>
                                <div class="chat-bubble">
                                    <p class="mb-1">Hi! Welcome to Eventia Events Management! 👋</p>
                                    <p class="mb-0">I'm here to help you plan your perfect event. Let's get started!</p>
                                    <small class="text-muted d-block mt-1">Just now</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div id="chatOptions" class="mb-3">
                        <!-- Dynamic options will appear here -->
                    </div>
                    <div class="input-group" id="chatInputArea" style="display: none;">
                        <input type="text" class="form-control" id="chatInput" placeholder="Type your message...">
                        <button class="btn btn-primary" type="button" name="send" id="send" onclick="sendMessage()">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                    <!-- Navigation Controls -->
                    <div id="navigationControls" class="d-flex justify-content-between mt-3" style="display: none !important;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="backButton" onclick="goBack()">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="cancelButton" onclick="cancelBooking()">
                            <i class="bi bi-x-circle me-1"></i> Cancel Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Summary Panel (Right Side) -->
        <div class="col-lg-5">
            <div class="card booking-summary-card">
                <div class="card-header">
                    <h5 class="mb-0">Booking Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Current Stage</h6>
                        <div class="progress booking-progress mb-2">
                            <div class="progress-bar" id="summaryProgress" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="currentStage">Stage 1: Select Event Type</small>
                    </div>

                    <hr>

                    <!-- Order Summary -->
                    <div class="order-summary mb-4">
                        <h6 class="fw-bold mb-3">Order Details</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody id="orderSummary">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No items selected yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <h5 class="text-primary" id="totalAmount">Total: ₱0.00</h5>
                        </div>
                    </div>

                    <hr>

                    <div class="summary-item">
                        <div class="summary-label">Event Type</div>
                        <div class="summary-value" id="summaryEventType">-</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Venue</div>
                        <div class="summary-value" id="summaryVenue">-</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Selected Services</div>
                        <ul class="summary-services-list" id="summaryServices">
                            <li class="text-muted">None selected yet</li>
                        </ul>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Special Requests</div>
                        <div class="summary-value text-muted" id="summaryRequests">None</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Client Information</div>
                        <div class="summary-value" id="summaryClient">-</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Event Date</div>
                        <div class="summary-value" id="summaryDate">-</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Payment Method</div>
                        <div class="summary-value" id="summaryPayment">-</div>
                    </div>

                    <hr>

                    <div class="mb-0">
                        <div class="summary-label">Booking Reference</div>
                        <div class="text-primary fw-bold" id="bookingReferenceDisplay">EVT-<span id="refNumber"></span></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Hidden Booking Form - ENHANCED WITH ALL REQUIRED FIELDS -->
<form id="bookingForm" name="bookingForm" method="POST" enctype="multipart/form-data" style="display: none;">
    <input type="hidden" name="booking_type" value="guided">
    <input type="hidden" name="booking_reference" id="bookingReference">
    
    <!-- Venue Information -->
    <input type="hidden" name="venue_type" id="venueType">
    <input type="hidden" name="venue_id" id="venueId">
    <input type="hidden" name="event_location" id="eventLocation">
    <input type="hidden" name="full_address" id="fullAddress">
    
    <!-- Package Information -->
    <input type="hidden" name="package" id="formPackage">
    <input type="hidden" name="eventType" id="formEventType">
    
    <!-- Client Information - MATCHING SELF-BOOKING FIELDS -->
    <input type="hidden" name="contact_name" id="formContactName">
    <input type="hidden" name="contact_email" id="formContactEmail">
    <input type="hidden" name="contact_phone" id="formContactPhone">
    <input type="hidden" name="alternate_phone" id="formAlternatePhone">
    <input type="hidden" name="company_name" id="formCompanyName">
    <input type="hidden" name="backup_email" id="formBackupEmail">
    <input type="hidden" name="event_date" id="formEventDate">
    <input type="hidden" name="event_time" id="formEventTime">
    <input type="hidden" name="guest_count" id="formGuestCount">
    <input type="hidden" name="preferred_contact" id="formPreferredContact">
    <input type="hidden" name="special_instructions" id="formSpecialInstructions">
    
    <!-- Payment Information -->
    <input type="hidden" name="amount" id="formAmount">
    <input type="hidden" name="firstName" id="formFirstName">
    <input type="hidden" name="lastName" id="formLastName">
    <input type="hidden" name="email" id="formEmail">
    <input type="hidden" name="phone" id="formPhone">
    
    <!-- Services - will be added dynamically -->
    <div id="servicesContainer">
        <!-- Services will be added here as hidden inputs -->
    </div>
    
    <!-- Customizations - will be added dynamically -->
    <div id="customizationsContainer">
        <!-- Customizations will be added here as hidden inputs -->
    </div>
</form>

<!-- Customization Modal - ADDED FROM SELF_BOOKING -->
<div class="modal fade" id="customizationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customizationModalTitle">Customize Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customizationModalBody">
                <!-- Customization options will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCustomizationBtn">Save Customization</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal - UPDATED TO USE AJAX LIKE SELF_BOOKING -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Your Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle me-2"></i>Payment Instructions</h6>
                    <p class="mb-2">You will be redirected to PayMongo to complete your payment securely.</p>
                    <p class="mb-0"><strong>Supported Payment Methods:</strong> Credit/Debit Card, GCash, GrabPay</p>
                </div>
                
                <div class="order-summary mb-4">
                    <h6 class="mb-3">Final Order Summary</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody id="paymentOrderSummary">
                                <!-- Order summary will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <h4 class="text-primary" id="paymentTotalAmount">Total: ₱0.00</h4>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> and understand that this booking is subject to confirmation.
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <!-- CHANGED: Now uses AJAX submission like self_booking -->
                <button type="button" class="btn btn-success" id="proceedToPaymongoBtn">Proceed to Paymongo</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this booking? You will be redirected to our packages page and all your progress will be lost.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Booking</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancel()">Yes, Cancel Booking</button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Availability Modal - ADDED FROM SELF_BOOKING -->
<div class="modal fade" id="calendarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Available Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="calendar-container">
                            <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="prevMonth">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <h5 class="mb-0" id="currentMonthYear">January 2024</h5>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="nextMonth">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                            <div class="calendar">
                                <div class="calendar-weekdays d-flex border-bottom">
                                    <div class="weekday text-center">Sun</div>
                                    <div class="weekday text-center">Mon</div>
                                    <div class="weekday text-center">Tue</div>
                                    <div class="weekday text-center">Wed</div>
                                    <div class="weekday text-center">Thu</div>
                                    <div class="weekday text-center">Fri</div>
                                    <div class="weekday text-center">Sat</div>
                                </div>
                                <div class="calendar-days" id="calendarDays">
                                    <!-- Calendar days will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="availability-info">
                            <h6>Availability Legend</h6>
                            <div class="legend-item mb-2">
                                <span class="availability-dot available"></span>
                                <small>Available</small>
                            </div>
                            <div class="legend-item mb-2">
                                <span class="availability-dot partially-available"></span>
                                <small>Limited</small>
                            </div>
                            <div class="legend-item mb-2">
                                <span class="availability-dot unavailable"></span>
                                <small>Booked</small>
                            </div>
                            
                            <div class="selected-date-info mt-4 p-3 bg-light rounded">
                                <h6>Selected Date</h6>
                                <div id="selectedDateInfo" class="text-muted">No date selected</div>
                                <div id="selectedDateAvailability" class="mt-2"></div>
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Dates are subject to venue and service availability
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmDate">Select Date</button>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Booking Confirmation</h6>
                <p>Your booking is subject to confirmation based on venue and service availability. We will contact you within 24 hours to confirm your booking.</p>
                
                <h6>Payment Terms</h6>
                <p>A 50% downpayment is required to secure your booking. The remaining balance must be paid 7 days before the event date.</p>
                
                <h6>Cancellation Policy</h6>
                <p>Cancellations made 30 days before the event will receive a full refund. Cancellations within 15-29 days will receive a 50% refund. No refund for cancellations within 14 days of the event.</p>
                
                <h6>Changes to Booking</h6>
                <p>Changes to your booking are subject to availability and must be requested at least 7 days before the event. Additional charges may apply.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>


<script>
// Prepare conversation flow data
const packagesData = <?php echo json_encode($packages_js); ?>;
const servicesData = <?php echo json_encode($services_js); ?>;
const phpServices = <?php echo json_encode($services); ?>;
</script>

<script>
// Calendar functionality - SAME AS SELF_BOOKING
let currentDate = new Date();
let selectedCalendarDate = null;

// Initialize calendar for guided booking
document.addEventListener('DOMContentLoaded', function() {
    console.log('📅 Initializing calendar functionality for guided booking...');
    
    // Calendar navigation
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const confirmDateBtn = document.getElementById('confirmDate');
    
    if (prevMonthBtn) prevMonthBtn.addEventListener('click', previousMonth);
    if (nextMonthBtn) nextMonthBtn.addEventListener('click', nextMonth);
    if (confirmDateBtn) confirmDateBtn.addEventListener('click', confirmDateSelection);
    
    // Initialize calendar
    generateCalendar(currentDate);
    console.log('✅ Calendar initialized for guided booking');
});

// Function to open calendar from guided booking flow
function openCalendarInGuided() {
    console.log('📅 Opening calendar modal from guided booking');
    const calendarModal = new bootstrap.Modal(document.getElementById('calendarModal'));
    calendarModal.show();
    
    // Generate calendar for current month
    generateCalendar(currentDate);
}

function generateCalendar(date) {
    console.log('🔄 Generating calendar for:', date.toLocaleDateString());
    
    const calendarDays = document.getElementById('calendarDays');
    const currentMonthYear = document.getElementById('currentMonthYear');
    
    if (!calendarDays || !currentMonthYear) {
        console.error('❌ Calendar elements not found');
        return;
    }
    
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
    
    console.log(`📅 Calendar info: Start day: ${startingDay}, Total days: ${totalDays}`);
    
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
        dayElement.setAttribute('data-date', `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`);
        
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
            // Check availability for this date
            checkAndSetAvailability(currentDate, dayElement);
        }
        
        calendarDays.appendChild(dayElement);
    }
}

// Availability checking functions (same as self_booking)
async function checkAndSetAvailability(date, element) {
    try {
        console.log(`🔍 Checking availability for: ${date.toDateString()}`);
        const availability = await getDateAvailability(date);
        console.log(`📊 ${date.toDateString()} - Status: ${availability.status}, Message: ${availability.message}`);
        
        // Clear any existing availability classes
        element.classList.remove('available', 'partially-available', 'unavailable');
        
        // Add the correct availability class
        element.classList.add(availability.status);
        element.title = availability.message;
        
        // Only add click event if the date is available or partially available
        if (availability.status !== 'unavailable') {
            element.style.cursor = 'pointer';
            element.addEventListener('click', function() {
                console.log(`🎯 Clicked on ${date.toDateString()} with status: ${availability.status}`);
                selectDate(date, element);
            });
            console.log(`✅ ${date.toDateString()} - Click event added`);
        } else {
            element.style.cursor = 'not-allowed';
            console.log(`🚫 ${date.toDateString()} - No click event added (unavailable)`);
        }
        
    } catch (error) {
        console.error('❌ Error in checkAndSetAvailability:', error);
        // Fallback: mark as available and add click event
        element.classList.remove('available', 'partially-available', 'unavailable');
        element.classList.add('available');
        element.title = 'Available';
        element.style.cursor = 'pointer';
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
        
        // Use GET instead of POST for simplicity
        const response = await fetch(`functions/get_availability.php?month=${date.getMonth() + 1}&year=${date.getFullYear()}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        console.log('📊 Server response for availability:', data);
        
        if (data.error || !data.success) {
            console.error('Error from server:', data.error);
            return getFallbackAvailability(date);
        }
        
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
        console.error('❌ Error checking availability:', error);
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

function selectDate(date, element) {
    console.log('🟢 selectDate called with:', date);
    
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

// Example of how to integrate in your conversation flow
function askForEventDate() {
    addAdminMessage("Now let's choose your event date. I'll show you our availability calendar.");
    
    // Show calendar option
    showOptions([
        {
            text: "📅 Open Availability Calendar",
            action: function() {
                openCalendarInGuided();
            }
        }
    ]);
}

async function updateSelectedDateInfo(date) {
    const selectedDateInfo = document.getElementById('selectedDateInfo');
    const selectedDateAvailability = document.getElementById('selectedDateAvailability');
    
    if (!selectedDateInfo || !selectedDateAvailability) {
        console.error('❌ Selected date info elements not found');
        return;
    }
    
    const availability = await getDateAvailability(date);
    
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
    
    // Check availability one more time before confirming
    getDateAvailability(selectedCalendarDate).then(availability => {
        if (availability.status === 'unavailable') {
            alert('The selected date is not available. Please choose another date.');
            return;
        }
        
        if (availability.status === 'partially-available') {
            if (!confirm('This date has limited availability. Would you like to proceed?')) {
                return;
            }
        }
        
        // Format date for input field (YYYY-MM-DD)
        const formattedDate = selectedCalendarDate.toISOString().split('T')[0];
        
        // Set the date in the form field
        document.getElementById('eventDate').value = formattedDate;
        document.getElementById('formEventDate').value = formattedDate;
        
        // Update the summary display
        document.getElementById('summaryDate').textContent = formattedDate;
        
        // Close the modal
        const calendarModal = bootstrap.Modal.getInstance(document.getElementById('calendarModal'));
        if (calendarModal) {
            calendarModal.hide();
        }
        
        console.log('✅ Date selected for guided booking:', formattedDate);
        
        // Show confirmation in chat
        addAdminMessage(`Great! I've noted your preferred date: ${formattedDate}. Please complete the rest of the form and click "Submit Information".`);
    });
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar(currentDate);
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar(currentDate);
}

// Add to global scope for guided booking
window.openCalendarInGuided = openCalendarInGuided;
window.selectDate = selectDate;
</script>

<?php include __DIR__."/components/footer.php"; ?>