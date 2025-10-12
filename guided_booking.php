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

<!-- Additional CSS -->
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
                        <button class="btn btn-primary" type="button" onclick="sendMessage()">
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
// Global variables
let bookingData = {
    eventType: '',
    venue: '',
    venueType: '',
    venueId: null,
    services: [],
    customizations: {},
    clientInfo: {},
    paymentMethod: '',
    packagePrice: 0,
    servicePrices: {},
    totalAmount: 0
};

let currentStage = 1;
const totalStages = 6;
let currentCustomizingService = null;
let serviceCustomizations = {};
let stageHistory = []; // Track navigation history

// Prepare conversation flow data
const packagesData = <?php echo json_encode($packages_js); ?>;
const servicesData = <?php echo json_encode($services_js); ?>;
const phpServices = <?php echo json_encode($services); ?>;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Initializing guided booking page...');
    console.log('✅ Bootstrap available:', typeof bootstrap !== 'undefined');
    
    generateBookingReference();
    updateTotalAmount();
    showNavigationControls();
    
    // FIXED: Initialize event listener for save customization button
    document.getElementById('saveCustomizationBtn').addEventListener('click', saveCustomization);
    
    // FIXED: Initialize event listener for payment button
    document.getElementById('proceedToPaymongoBtn').addEventListener('click', submitPaymentForm);
    
    setTimeout(() => startStage(1), 1000);
});

// Generate booking reference
function generateBookingReference() {
    const timestamp = Date.now().toString();
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    const reference = 'EVT-' + timestamp.slice(-6) + random;
    document.getElementById('bookingReference').value = reference;
    document.getElementById('refNumber').textContent = reference.slice(4);
}

// Show/hide navigation controls
function showNavigationControls() {
    const navControls = document.getElementById('navigationControls');
    if (currentStage > 1) {
        navControls.style.display = 'flex';
    } else {
        navControls.style.display = 'none';
    }
}

// Navigation functions
function goBack() {
    if (stageHistory.length > 0) {
        const previousStage = stageHistory.pop();
        startStage(previousStage, true);
    }
}

function cancelBooking() {
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    cancelModal.show();
}

function confirmCancel() {
    // Redirect to packages.php instead of resetting
    window.location.href = "packages.php";
}

// NEW FUNCTION: Update form fields from booking data
function updateFormFields() {
    // Basic booking info
    document.getElementById('formPackage').value = bookingData.eventType;
    document.getElementById('formEventType').value = bookingData.eventType;
    document.getElementById('formAmount').value = bookingData.totalAmount;
    
    // Venue info
    document.getElementById('venueType').value = bookingData.venueType;
    document.getElementById('venueId').value = bookingData.venueId || '';
    document.getElementById('eventLocation').value = bookingData.venue || '';
    document.getElementById('fullAddress').value = bookingData.venue || '';
    
    // Client info
    if (bookingData.clientInfo.name) {
        document.getElementById('formContactName').value = bookingData.clientInfo.name;
        document.getElementById('formFirstName').value = bookingData.clientInfo.name.split(' ')[0] || 'Customer';
        document.getElementById('formLastName').value = bookingData.clientInfo.name.split(' ').slice(1).join(' ') || '';
    }
    if (bookingData.clientInfo.email) {
        document.getElementById('formContactEmail').value = bookingData.clientInfo.email;
        document.getElementById('formEmail').value = bookingData.clientInfo.email;
        document.getElementById('formBackupEmail').value = bookingData.clientInfo.email;
    }
    if (bookingData.clientInfo.phone) {
        document.getElementById('formContactPhone').value = bookingData.clientInfo.phone;
        document.getElementById('formAlternatePhone').value = bookingData.clientInfo.phone;
        document.getElementById('formPhone').value = bookingData.clientInfo.phone;
    }
    if (bookingData.clientInfo.date) {
        document.getElementById('formEventDate').value = bookingData.clientInfo.date;
    }
    if (bookingData.clientInfo.time) {
        document.getElementById('formEventTime').value = bookingData.clientInfo.time;
    }
    if (bookingData.clientInfo.guestCount) {
        document.getElementById('formGuestCount').value = bookingData.clientInfo.guestCount;
    }
    if (bookingData.clientInfo.instructions) {
        document.getElementById('formSpecialInstructions').value = bookingData.clientInfo.instructions;
    }
    
    // Default values for optional fields
    document.getElementById('formPreferredContact').value = 'Any';
    document.getElementById('formCompanyName').value = '';
    
    // Update services in form
    updateServicesInForm();
    
    console.log('Form fields updated for payment');
}

// NEW FUNCTION: Update services as hidden inputs in form
function updateServicesInForm() {
    const servicesContainer = document.getElementById('servicesContainer');
    servicesContainer.innerHTML = '';
    
    bookingData.services.forEach(serviceId => {
        const service = phpServices.find(s => s.service_id == serviceId);
        if (service) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'services[]';
            input.value = service.service_name;
            servicesContainer.appendChild(input);
        }
    });
}

// Chat conversation flow
const conversationFlow = {
    1: { // Stage 1 - Select Event Type
        question: "What type of event are you planning?",
        options: packagesData,
        action: (value, selectedOption) => {
            bookingData.eventType = value;
            bookingData.packagePrice = parseFloat(selectedOption.price);
            // Update form fields
            document.getElementById('formPackage').value = value;
            document.getElementById('formEventType').value = value;
            updateSummary('eventType', value);
            updateOrderSummary();
            updateTotalAmount();
            addAdminMessage(`Great choice! Let's plan your ${value}. 🎉`);
            setTimeout(() => startStage(2), 1000);
        }
    },
    2: { // Stage 2 - Venue Selection
        question: "Would you like to use one of our available venues, or do you already have a location?",
        options: [
            { text: "Show me available venues", value: "show_venues" },
            { text: "I have my own venue", value: "own_venue" }
        ],
        action: (value) => {
            if (value === "show_venues") {
                bookingData.venueType = 'rental';
                showVenueOptions();
            } else {
                bookingData.venueType = 'own';
                showCustomVenueInput();
            }
        }
    },
    3: { // Stage 3 - Select Services
        question: `Perfect! Now, let's customize your ${bookingData.eventType}. Which services would you like to include?`,
        multiSelect: true,
        options: servicesData,
        action: (values) => {
            bookingData.services = values;
            // Store service prices
            values.forEach(serviceId => {
                const service = phpServices.find(s => s.service_id == serviceId);
                if (service) {
                    bookingData.servicePrices[serviceId] = parseFloat(service.base_price);
                }
            });
            updateServicesSummary(values);
            updateOrderSummary();
            updateTotalAmount();
            // Update services in form
            updateServicesInForm();
            addAdminMessage(`Excellent selections! I've added ${values.length} service(s) to your booking. ✨`);
            
            // Show customization options for selected services
            setTimeout(() => {
                showServiceCustomizations(values);
            }, 1000);
        }
    },
    4: { // Stage 4 - Personal Information
        question: "Now I need to collect some information. Can I get your full name, email, contact number, and preferred event date?",
        requiresInput: true,
        action: () => {
            showPersonalInfoForm();
        }
    },
    5: { // Stage 5 - Payment Method
        question: "Almost done! How would you like to handle the payment?",
        options: [
            { text: "PayMongo (Credit Card, GCash, GrabPay)", value: "paymongo" },
            { text: "Bank Transfer", value: "Bank Transfer" },
            { text: "PayPal", value: "PayPal" }
        ],
        action: (value) => {
            bookingData.paymentMethod = value;
            updateSummary('payment', value);
            
            if (value === 'paymongo') {
                showPayMongoPayment();
            } else {
                showAlternativePaymentDetails(value);
            }
        }
    }
};

function startStage(stage, isGoingBack = false) {
    if (!isGoingBack && currentStage !== stage) {
        stageHistory.push(currentStage);
    }
    
    currentStage = stage;
    updateProgress();
    showNavigationControls();
    
    const stageNames = [
        '',
        'Select Event Type',
        'Select Venue',
        'Select Services',
        'Enter Personal Information',
        'Choose Payment Method',
        'Confirmation'
    ];
    
    document.getElementById('currentStage').textContent = `Stage ${stage}: ${stageNames[stage]}`;

    if (stage <= 5) {
        const flow = conversationFlow[stage];
        if (!isGoingBack) {
            addAdminMessage(flow.question);
        }
        
        setTimeout(() => {
            if (flow.options && !flow.requiresInput) {
                showOptions(flow.options, flow.action, flow.multiSelect);
            } else if (flow.requiresInput) {
                flow.action();
            }
        }, 500);
    }
}

function showOptions(options, callback, multiSelect = false) {
    const optionsContainer = document.getElementById('chatOptions');
    optionsContainer.innerHTML = '';

    if (multiSelect) {
        // Show checkboxes for multi-select
        options.forEach(option => {
            const div = document.createElement('div');
            div.className = 'form-check mb-2';
            div.innerHTML = `
                <input class="form-check-input service-checkbox" type="checkbox" value="${option.value}" id="opt_${option.value}" data-price="${option.price}">
                <label class="form-check-label" for="opt_${option.value}">
                    ${option.text}
                </label>
            `;
            optionsContainer.appendChild(div);
        });

        // Add confirm button
        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'btn btn-primary w-100 mt-2';
        confirmBtn.innerHTML = 'Confirm Selection <i class="bi bi-check ms-2"></i>';
        confirmBtn.onclick = () => {
            const selected = Array.from(document.querySelectorAll('.service-checkbox:checked')).map(cb => cb.value);
            if (selected.length === 0) {
                alert('Please select at least one service');
                return;
            }
            const selectedNames = selected.map(id => {
                const option = options.find(opt => opt.value == id);
                return option ? option.name : id;
            });
            addUserMessage(selectedNames.join(', '));
            optionsContainer.innerHTML = '';
            callback(selected);
        };
        optionsContainer.appendChild(confirmBtn);
    } else {
        // Show buttons for single select
        options.forEach(option => {
            const button = document.createElement('button');
            button.className = 'btn btn-outline-primary mb-2 me-2';
            button.textContent = option.text;
            button.onclick = () => {
                addUserMessage(option.text);
                optionsContainer.innerHTML = '';
                callback(option.value, option);
            };
            optionsContainer.appendChild(button);
        });
    }
}

function showVenueOptions() {
    addAdminMessage("Let me show you our available venues...");
    
    // Fetch venues from server
    fetch('get_venues.php')
        .then(response => response.json())
        .then(venues => {
            if (venues.length === 0) {
                addAdminMessage("Sorry, no venues are currently available. Would you like to provide your own venue address?");
                showCustomVenueInput();
                return;
            }

            const venueOptions = venues.map(v => ({ 
                text: `${v.venue_name} - ₱${parseFloat(v.price).toLocaleString()}`, 
                value: v.venue_id,
                name: v.venue_name,
                location: v.location
            }));
            venueOptions.push({ text: "I have my own venue", value: "own_venue", name: "Own Venue" });

            setTimeout(() => {
                showOptions(venueOptions, (value, selectedOption) => {
                    if (value === "own_venue") {
                        bookingData.venueType = 'own';
                        showCustomVenueInput();
                    } else {
                        bookingData.venueId = value;
                        bookingData.venue = selectedOption.name;
                        document.getElementById('venueId').value = value;
                        document.getElementById('eventLocation').value = selectedOption.name;
                        document.getElementById('fullAddress').value = selectedOption.location;
                        updateSummary('venue', selectedOption.name);
                        addAdminMessage(`Excellent choice! ${selectedOption.name} it is! 🏛️`);
                        setTimeout(() => startStage(3), 1000);
                    }
                });
            }, 1000);
        })
        .catch(error => {
            console.error('Error loading venues:', error);
            addAdminMessage("Sorry, I couldn't load the venues. Would you like to provide your own venue address?");
            showCustomVenueInput();
        });
}

function showCustomVenueInput() {
    addAdminMessage("Please type your venue address:");
    
    const chatInput = document.getElementById('chatInputArea');
    chatInput.style.display = 'flex';
    
    const input = document.getElementById('chatInput');
    input.focus();
    
    // Enhanced event listener for venue input
    const handleVenueInput = (e) => {
        if (e.key === 'Enter' && input.value.trim()) {
            const venue = input.value.trim();
            addUserMessage(venue);
            bookingData.venue = venue;
            document.getElementById('eventLocation').value = venue;
            document.getElementById('fullAddress').value = venue;
            updateSummary('venue', 'Own Venue: ' + venue);
            chatInput.style.display = 'none';
            input.value = '';
            
            // Remove event listeners to prevent multiple bindings
            input.removeEventListener('keypress', handleVenueInput);
            input.onkeypress = null;
            
            addAdminMessage(`Got it! We'll set up at ${venue}. 📍`);
            setTimeout(() => startStage(3), 1000);
        }
    };
    
    // Add event listener
    input.addEventListener('keypress', handleVenueInput);
}

// Service customization flow - FIXED
function showServiceCustomizations(serviceIds) {
    // Reset current customizing service
    currentCustomizingService = null;
    
    const servicesToCustomize = serviceIds.filter(id => {
        const service = phpServices.find(s => s.service_id == id);
        return service && service.customizable && !serviceCustomizations[id];
    });

    console.log('🔄 Services to customize:', servicesToCustomize);

    if (servicesToCustomize.length === 0) {
        addAdminMessage("All services have been added! Let's move on to your information.");
        setTimeout(() => startStage(4), 1000);
        return;
    }

    addAdminMessage(`I see ${servicesToCustomize.length} service(s) that can be customized. Let me show you the options...`);
    
    // Start with first service
    showCustomizationModalForService(servicesToCustomize, 0);
}

function showCustomizationModalForService(servicesToCustomize, index) {
    if (index >= servicesToCustomize.length) {
        // All services customized
        addAdminMessage("All customizations have been set! Let's move on to your information.");
        setTimeout(() => startStage(4), 1000);
        return;
    }
    
    const serviceId = servicesToCustomize[index];
    const service = phpServices.find(s => s.service_id == serviceId);
    
    if (!service) {
        // Skip to next service if not found
        showCustomizationModalForService(servicesToCustomize, index + 1);
        return;
    }
    
    currentCustomizingService = service.service_id;
    
    const modalTitle = document.getElementById('customizationModalTitle');
    const modalBody = document.getElementById('customizationModalBody');
    
    modalTitle.textContent = 'Customize: ' + service.service_name;
    
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
            Your customization requests will be reviewed and confirmed by our team.
        </div>
    `;
    
    // Load existing customization if any
    if (serviceCustomizations[service.service_id]) {
        document.getElementById('customPackage').value = serviceCustomizations[service.service_id].package || 'basic';
        document.getElementById('customUnits').value = serviceCustomizations[service.service_id].units || 4;
        document.getElementById('customRequirements').value = serviceCustomizations[service.service_id].requirements || '';
        document.getElementById('customNotes').value = serviceCustomizations[service.service_id].notes || '';
    }
    
    // Store the current index and services array for continuation
    const modalElement = document.getElementById('customizationModal');
    
    // Remove any existing event listeners
    const newModalElement = modalElement.cloneNode(true);
    modalElement.parentNode.replaceChild(newModalElement, modalElement);
    
    // Add event listener for modal hidden event
    newModalElement.addEventListener('hidden.bs.modal', function() {
        console.log('Modal hidden, checking if we need to show next service');
    });
    
    const customizationModal = new bootstrap.Modal(newModalElement);
    
    // Update the save button event listener
    const saveBtn = document.getElementById('saveCustomizationBtn');
    const newSaveBtn = saveBtn.cloneNode(true);
    saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
    
    newSaveBtn.onclick = function() {
        saveCustomization(servicesToCustomize, index, customizationModal);
    };
    
    customizationModal.show();
    console.log(`✅ Showing customization modal for service ${index + 1}/${servicesToCustomize.length}:`, service.service_name);
}

function saveCustomization(servicesToCustomize, currentIndex, modal) {
    if (!currentCustomizingService) {
        console.error('❌ No service selected for customization');
        return;
    }
    
    console.log('💾 Saving customization for service:', currentCustomizingService);
    
    const customization = {
        package: document.getElementById('customPackage').value,
        units: document.getElementById('customUnits').value,
        requirements: document.getElementById('customRequirements').value,
        notes: document.getElementById('customNotes').value,
        timestamp: new Date().toISOString()
    };
    
    serviceCustomizations[currentCustomizingService] = customization;
    bookingData.customizations[currentCustomizingService] = customization;
    
    // Add to hidden form
    const customizationsContainer = document.getElementById('customizationsContainer');
    let existingInput = document.querySelector(`input[name="customization[${currentCustomizingService}]"]`);
    if (!existingInput) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `customization[${currentCustomizingService}]`;
        input.value = JSON.stringify(customization);
        customizationsContainer.appendChild(input);
    } else {
        existingInput.value = JSON.stringify(customization);
    }
    
    // Hide modal
    modal.hide();
    
    const service = phpServices.find(s => s.service_id == currentCustomizingService);
    addAdminMessage(`${service.service_name} customization saved! ✅`);
    
    // Show next service after a short delay
    setTimeout(() => {
        showCustomizationModalForService(servicesToCustomize, currentIndex + 1);
    }, 1000);
}

function showPersonalInfoForm() {
    const optionsContainer = document.getElementById('chatOptions');
    optionsContainer.innerHTML = `
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="clientName" placeholder="Anna Marie Santos">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="clientEmail" placeholder="annas@gmail.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Number *</label>
                    <input type="tel" class="form-control" id="clientPhone" placeholder="0917-555-1234">
                </div>
                <div class="mb-3">
                    <label class="form-label">Preferred Event Date *</label>
                    <input type="date" class="form-control" id="eventDate" min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Event Time *</label>
                    <input type="time" class="form-control" id="eventTime">
                </div>
                <div class="mb-3">
                    <label class="form-label">Number of Guests</label>
                    <input type="number" class="form-control" id="guestCount" min="1" value="50">
                </div>
                <div class="mb-3">
                    <label class="form-label">Special Instructions</label>
                    <textarea class="form-control" id="specialInstructions" rows="3" placeholder="Any special requirements..."></textarea>
                </div>
                <button class="btn btn-primary w-100" onclick="submitPersonalInfo()">
                    Submit Information <i class="bi bi-check ms-2"></i>
                </button>
            </div>
        </div>
    `;
}

function submitPersonalInfo() {
    const name = document.getElementById('clientName').value.trim();
    const email = document.getElementById('clientEmail').value.trim();
    const phone = document.getElementById('clientPhone').value.trim();
    const date = document.getElementById('eventDate').value;
    const time = document.getElementById('eventTime').value;
    const guestCount = document.getElementById('guestCount').value;
    const instructions = document.getElementById('specialInstructions').value.trim();

    if (!name || !email || !phone || !date || !time) {
        alert('Please fill in all required fields');
        return;
    }

    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return;
    }

    bookingData.clientInfo = { name, email, phone, date, time, guestCount, instructions };
    
    // Update form fields
    updateFormFields();
    
    addUserMessage(`Name: ${name}, Email: ${email}, Phone: ${phone}, Date: ${date}, Time: ${time}, Guests: ${guestCount}`);
    
    updateSummary('client', `${name}<br><small>${email}<br>${phone}</small>`);
    updateSummary('date', `${date} at ${time}`);
    if (instructions) {
        updateSummary('requests', instructions);
    }
    
    document.getElementById('chatOptions').innerHTML = '';
    
    addAdminMessage(`Thank you, ${name}! I have all your information. 👍`);
    setTimeout(() => startStage(5), 1000);
}

// CHANGED: Now uses AJAX submission like self_booking
function showPayMongoPayment() {
    // Update form fields with current data
    updateFormFields();
    
    // Update payment summary for modal
    updatePaymentOrderSummary();
    
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

function showAlternativePaymentDetails(method) {
    let message = '';
    
    switch(method) {
        case 'Bank Transfer':
            message = `You can transfer to any of these accounts:<br><strong>BDO:</strong> 1234-5678-9012<br><strong>BPI:</strong> 9876-5432-1098<br><strong>Account Name:</strong> Eventia Events Management 🏦`;
            break;
        case 'PayPal':
            message = `Send your payment to:<br><strong>PayPal Email:</strong> payments@eventia.com<br><br>Perfect for digital transactions! 💻`;
            break;
    }
    
    addAdminMessage(message);
    
    // For alternative payments, we can still use form submission but might need different handling
    setTimeout(() => {
        addAdminMessage("Your booking is now being processed. Let me finalize everything...");
        // For now, we'll use the same form submission for consistency
        setTimeout(() => submitBookingToDatabase(), 2000);
    }, 2000);
}

// NEW FUNCTION: Submit payment via AJAX (like self_booking)
function submitPaymentForm() {
    if (!document.getElementById('terms').checked) {
        alert('Please agree to the Terms and Conditions to proceed.');
        return;
    }

    // Final update of form fields
    updateFormFields();
    
    // Validate required fields
    const requiredFields = ['contact_name', 'contact_email', 'contact_phone', 'event_date', 'amount'];
    for (let field of requiredFields) {
        const element = document.querySelector(`[name="${field}"]`);
        if (!element || !element.value) {
            alert('Missing required information. Please complete all booking steps.');
            return;
        }
    }

    addAdminMessage("Processing your payment with PayMongo...");
    
    // Hide modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
    modal.hide();
    
    // Create FormData from the booking form
    const formData = new FormData(document.getElementById('bookingForm'));
    
    // Show loading state
    const paymentBtn = document.getElementById('proceedToPaymongoBtn');
    const originalText = paymentBtn.innerHTML;
    paymentBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Processing...';
    paymentBtn.disabled = true;
    
    // Submit via AJAX like self_booking
    fetch('paymongo-payment-method/create_payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addAdminMessage(`Payment processed successfully! Redirecting to PayMongo...`);
            console.log('✅ Payment successful, redirecting to:', data.checkout_url);
            // Redirect to PayMongo checkout
            window.location.href = data.checkout_url;
        } else {
            addAdminMessage(`Payment failed: ${data.error}. Please try again or contact support.`);
            console.error('❌ Payment failed:', data.error);
            // Reset button
            paymentBtn.innerHTML = originalText;
            paymentBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('❌ Payment request failed:', error);
        addAdminMessage('Payment request failed. Please check your connection and try again.');
        // Reset button
        paymentBtn.innerHTML = originalText;
        paymentBtn.disabled = false;
    });
}

function submitBookingToDatabase() {
    // For alternative payment methods, we might need different handling
    // For now, we'll show a confirmation message
    confirmBooking();
}

function confirmBooking() {
    currentStage = 6;
    updateProgress();
    document.getElementById('currentStage').textContent = 'Stage 6: Confirmation';
    
    addAdminMessage(`🎉 <strong>Congratulations!</strong> Your booking is now confirmed under Reference Code <strong class="text-primary">${document.getElementById('bookingReference').value}</strong><br><br>You'll receive an email summary shortly. Thank you for choosing Eventia!`);
}

function updateOrderSummary() {
    const summaryContainer = document.getElementById('orderSummary');
    let total = bookingData.packagePrice;
    let summaryHTML = '';
    
    // Package
    if (bookingData.eventType) {
        summaryHTML += `
            <tr>
                <td><strong>Package</strong></td>
                <td>${bookingData.eventType}</td>
                <td class="text-end">₱${bookingData.packagePrice.toLocaleString()}</td>
            </tr>
        `;
    }
    
    // Services
    bookingData.services.forEach(serviceId => {
        const service = phpServices.find(s => s.service_id == serviceId);
        if (service) {
            const servicePrice = bookingData.servicePrices[serviceId] || 0;
            total += servicePrice;
            summaryHTML += `
                <tr>
                    <td><strong>Service</strong></td>
                    <td>${service.service_name}</td>
                    <td class="text-end">₱${servicePrice.toLocaleString()}</td>
                </tr>
            `;
        }
    });
    
    summaryContainer.innerHTML = summaryHTML || '<tr><td colspan="3" class="text-center text-muted">No items selected</td></tr>';
    bookingData.totalAmount = total;
    updateTotalAmount();
}

function updatePaymentOrderSummary() {
    const summaryContainer = document.getElementById('paymentOrderSummary');
    let total = bookingData.packagePrice;
    let summaryHTML = '';
    
    // Package
    if (bookingData.eventType) {
        summaryHTML += `
            <tr>
                <td><strong>Package</strong></td>
                <td>${bookingData.eventType}</td>
                <td class="text-end">₱${bookingData.packagePrice.toLocaleString()}</td>
            </tr>
        `;
    }
    
    // Services
    bookingData.services.forEach(serviceId => {
        const service = phpServices.find(s => s.service_id == serviceId);
        if (service) {
            const servicePrice = bookingData.servicePrices[serviceId] || 0;
            total += servicePrice;
            summaryHTML += `
                <tr>
                    <td><strong>Service</strong></td>
                    <td>${service.service_name}</td>
                    <td class="text-end">₱${servicePrice.toLocaleString()}</td>
                </tr>
            `;
        }
    });
    
    summaryContainer.innerHTML = summaryHTML;
    document.getElementById('paymentTotalAmount').textContent = `Total: ₱${total.toLocaleString()}`;
}

function updateTotalAmount() {
    document.getElementById('totalAmount').textContent = `Total: ₱${bookingData.totalAmount.toLocaleString()}`;
    // Also update the form amount field
    document.getElementById('formAmount').value = bookingData.totalAmount;
}

function addAdminMessage(text, className = '') {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message admin-message mb-3 ${className}`;
    messageDiv.innerHTML = `
        <div class="d-flex align-items-start">
            <div class="chat-avatar me-2">
                <div class="step-number" style="width: 35px; height: 35px; font-size: 0.9rem;">A</div>
            </div>
            <div class="chat-bubble">
                <p class="mb-0">${text}</p>
                <small class="text-muted d-block mt-1">Just now</small>
            </div>
        </div>
    `;
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

function addUserMessage(text) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message user-message mb-3';
    messageDiv.innerHTML = `
        <div class="d-flex align-items-start justify-content-end">
            <div class="chat-bubble">
                <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                <small class="text-muted d-block mt-1 text-end">Just now</small>
            </div>
            <div class="chat-avatar ms-2">
                <div class="step-number" style="width: 35px; height: 35px; font-size: 0.9rem; background: var(--light);">U</div>
            </div>
        </div>
    `;
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (message) {
        addUserMessage(message);
        input.value = '';
    }
}

function scrollToBottom() {
    const container = document.getElementById('chatContainer');
    container.scrollTop = container.scrollHeight;
}

function updateProgress() {
    const progress = (currentStage / totalStages) * 100;
    document.getElementById('summaryProgress').style.width = progress + '%';
}

function updateSummary(field, value) {
    switch(field) {
        case 'eventType':
            document.getElementById('summaryEventType').innerHTML = value;
            break;
        case 'venue':
            document.getElementById('summaryVenue').innerHTML = value;
            break;
        case 'requests':
            document.getElementById('summaryRequests').innerHTML = value;
            break;
        case 'client':
            document.getElementById('summaryClient').innerHTML = value;
            break;
        case 'date':
            document.getElementById('summaryDate').innerHTML = value;
            break;
        case 'payment':
            document.getElementById('summaryPayment').innerHTML = value;
            break;
    }
}

function updateServicesSummary(serviceIds) {
    const container = document.getElementById('summaryServices');
    container.innerHTML = '';
    
    if (serviceIds.length === 0) {
        container.innerHTML = '<li class="text-muted">None selected yet</li>';
        return;
    }
    
    serviceIds.forEach(serviceId => {
        const service = phpServices.find(s => s.service_id == serviceId);
        if (service) {
            const li = document.createElement('li');
            li.innerHTML = `<i class="bi bi-check-circle-fill text-success me-2"></i> ${service.service_name}`;
            container.appendChild(li);
        }
    });
}
</script>

<?php include __DIR__."/components/footer.php"; ?>