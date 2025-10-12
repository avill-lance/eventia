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
?>

<!-- Additional CSS -->
<link rel="stylesheet" href="css/booking-improvements.css">

<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Self-Booking</h1>
        <p class="lead mb-4">Book your perfect event in just a few steps</p>
    </div>
</div>

<!-- Booking Process -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Progress Bar -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center flex-fill step-indicator active" id="step-indicator-1">
                            <div class="step-number mx-auto">1</div>
                            <small class="d-block mt-2">Package</small>
                        </div>
                        <div class="text-center flex-fill step-indicator" id="step-indicator-2">
                            <div class="step-number mx-auto">2</div>
                            <small class="d-block mt-2">Venue</small>
                        </div>
                        <div class="text-center flex-fill step-indicator" id="step-indicator-3">
                            <div class="step-number mx-auto">3</div>
                            <small class="d-block mt-2">Services</small>
                        </div>
                        <div class="text-center flex-fill step-indicator" id="step-indicator-4">
                            <div class="step-number mx-auto">4</div>
                            <small class="d-block mt-2">Information</small>
                        </div>
                        <div class="text-center flex-fill step-indicator" id="step-indicator-5">
                            <div class="step-number mx-auto">5</div>
                            <small class="d-block mt-2">Payment</small>
                        </div>
                    </div>
                    <div class="progress booking-progress">
                        <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 20%"></div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <form id="bookingForm" name="bookingForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="booking_type" value="self">
                <input type="hidden" name="booking_reference" id="bookingReference">
                <input type="hidden" name="venue_type" id="venueType">
                <input type="hidden" name="venue_id" id="venueId">
                <input type="hidden" name="event_location" id="eventLocation">
                <input type="hidden" name="full_address" id="fullAddress">
                
                <!-- Step 1: Choose Package -->
                <div class="step active" id="step-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Step 1: Choose Your Event Package</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($packages as $package): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" 
                                                       id="package<?php echo $package['package_id']; ?>" 
                                                       value="<?php echo htmlspecialchars($package['package_name']); ?>" 
                                                       data-price="<?php echo $package['base_price']; ?>" required>
                                                <label class="form-check-label w-100" for="package<?php echo $package['package_id']; ?>">
                                                    <h5><?php echo htmlspecialchars($package['package_name']); ?></h5>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($package['package_description']); ?></p>
                                                    <h5 class="text-primary">₱<?php echo number_format($package['base_price'], 2); ?></h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4">
                                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next: Choose Venue</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Choose Venue -->
                <div class="step" id="step-2">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Step 2: Choose Your Venue</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card venue-option-card">
                                        <div class="card-body text-center">
                                            <div class="venue-icon mb-3">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <h5>Rent Our Venues</h5>
                                            <p class="text-muted">Choose from our curated selection of premium venues</p>
                                            <button type="button" class="btn btn-outline-primary" onclick="selectVenueType('rental')">Browse Venues</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card venue-option-card">
                                        <div class="card-body text-center">
                                            <div class="venue-icon mb-3">
                                                <i class="bi bi-house-door"></i>
                                            </div>
                                            <h5>Use Your Own Venue</h5>
                                            <p class="text-muted">We'll bring our services to your preferred location</p>
                                            <button type="button" class="btn btn-outline-primary" onclick="selectVenueType('own')">Enter Venue Details</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Venue Selection (Rental) -->
                            <div id="venueSelection" class="venue-selection" style="display: none;">
                                <h5 class="mb-3">Available Venues</h5>
                                <div class="row" id="venuesContainer">
                                    <!-- Venues will be loaded here via AJAX -->
                                </div>
                            </div>

                            <!-- Own Venue Form -->
                            <div id="ownVenueForm" class="own-venue-form" style="display: none;">
                                <h5 class="mb-3">Your Venue Details</h5>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="venue_address" class="form-label">Venue Address *</label>
                                            <input type="text" class="form-control" id="venue_address" name="venue_address" placeholder="Enter complete venue address">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="venue_city" class="form-label">City *</label>
                                            <input type="text" class="form-control" id="venue_city" name="venue_city" placeholder="Enter city">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="venue_postal" class="form-label">Postal Code *</label>
                                            <input type="text" class="form-control" id="venue_postal" name="venue_postal" placeholder="Enter postal code">
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Please ensure the venue is accessible and has adequate space for your event setup.
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Previous</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next: Add Services</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Additional Services -->
                <div class="step" id="step-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Step 3: Add Additional Services</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Enhance your event with these popular add-ons (optional)</p>
                            
                            <div class="row">
                                <?php foreach ($services as $service): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card service-option-card h-100">
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input service-checkbox" type="checkbox" 
                                                       name="services[]" value="<?php echo htmlspecialchars($service['service_name']); ?>" 
                                                       id="service-<?php echo $service['service_id']; ?>"
                                                       data-price="<?php echo $service['base_price']; ?>">
                                                <label class="form-check-label fw-bold" for="service-<?php echo $service['service_id']; ?>">
                                                    <?php echo htmlspecialchars($service['service_name']); ?>
                                                </label>
                                            </div>
                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($service['service_description']); ?></p>
                                            <p class="text-primary fw-bold mb-3">₱<?php echo number_format($service['base_price'], 2); ?></p>
                                            
                                            <!-- Service Details -->
                                            <?php if (!empty($service['details'])): ?>
                                            <div class="service-details">
                                                <h6 class="mb-2">Options:</h6>
                                                <ul class="small text-muted mb-3">
                                                    <?php foreach ($service['details'] as $detail): ?>
                                                    <li>
                                                        <?php echo htmlspecialchars($detail['detail_name']); ?>
                                                        (₱<?php echo number_format($detail['price_min'], 2); ?> - ₱<?php echo number_format($detail['price_max'], 2); ?>)
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Service Features -->
                                            <?php if (!empty($service['features'])): ?>
                                            <div class="service-features">
                                                <h6 class="mb-2">Includes:</h6>
                                                <div class="d-flex flex-wrap gap-1 mb-3">
                                                    <?php foreach ($service['features'] as $feature): ?>
                                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($feature); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Customization Button - ALWAYS SHOW -->
                                            <div class="customization-options mt-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="showCustomization(<?php echo $service['service_id']; ?>, '<?php echo htmlspecialchars($service['service_name']); ?>', <?php echo $service['customizable'] ? 'true' : 'false'; ?>)">
                                                    <i class="bi bi-gear me-1"></i> Customize Options
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next: Your Information</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Contact Information -->
                <div class="step" id="step-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Step 4: Your Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="contact_name" name="contact_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_phone" class="form-label">Contact Number *</label>
                                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alternate_phone" class="form-label">Alternate Contact Number</label>
                                        <input type="tel" class="form-control" id="alternate_phone" name="alternate_phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company/Organization Name</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="backup_email" class="form-label">Backup Email Address</label>
                                        <input type="email" class="form-control" id="backup_email" name="backup_email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="event_date" class="form-label">Event Date *</label>
                                        <input type="date" class="form-control" id="event_date" name="event_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="event_time" class="form-label">Event Time *</label>
                                        <input type="time" class="form-control" id="event_time" name="event_time" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="guest_count" class="form-label">Number of Guests</label>
                                        <input type="number" class="form-control" id="guest_count" name="guest_count" min="1" value="50">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="preferred_contact" class="form-label">Preferred Contact Method</label>
                                        <select class="form-select" id="preferred_contact" name="preferred_contact">
                                            <option value="Any">Any Method</option>
                                            <option value="Email">Email</option>
                                            <option value="Phone">Phone Call</option>
                                            <option value="SMS">SMS/Text</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="special_instructions" class="form-label">Special Instructions or Requests</label>
                                        <textarea class="form-control" id="special_instructions" name="special_instructions" rows="4" placeholder="Any special requirements, dietary restrictions, or additional information we should know..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Previous</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep(5)">Next: Payment</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Payment -->
                <div class="step" id="step-5">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Step 5: Payment</h4>
                        </div>
                        <div class="card-body">
                            <!-- Order Summary -->
                            <div class="order-summary mb-4">
                                <h5 class="mb-3">Order Summary</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody id="orderSummary">
                                            <!-- Order summary will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end">
                                    <h4 class="text-primary" id="amount">Total: ₱0.00</h4>
                                </div>
                            </div>

                            
                            <!-- Receipt Upload -->
                            <div id="receiptUpload" class="mb-4" style="display: none;">
                                <h5 class="mb-3">Upload Payment Receipt</h5>
                                <div class="mb-3">
                                    <label for="receipt" class="form-label">Payment Receipt (Image or PDF)</label>
                                    <input type="file" class="form-control" id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.gif,.pdf">
                                    <div class="form-text">
                                        Upload a clear photo or screenshot of your payment receipt. Accepted formats: JPG, PNG, GIF, PDF (Max: 5MB)
                                    </div>
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

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="prevStep(4)">Previous</button>
                                <button type="button" class="btn btn-success" id="submitBooking" name="submitBooking">Proceed to Paymongo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Customization Modal -->
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
                <button type="button" class="btn btn-primary" onclick="saveCustomization()">Save Customization</button>
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

<!-- Loading Spinner -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Processing your booking...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Service customization data
let serviceCustomizations = {};
let currentCustomizingService = null;

// Generate booking reference on page load
document.addEventListener('DOMContentLoaded', function() {
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
    
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const receiptUpload = document.getElementById('receiptUpload');
            if (this.value) {
                receiptUpload.style.display = 'block';
                document.getElementById('receipt').required = true;
            } else {
                receiptUpload.style.display = 'none';
                document.getElementById('receipt').required = false;
            }
        });
    });
});

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

function showCustomization(serviceId, serviceName, isCustomizable) {
    currentCustomizingService = serviceId;
    
    const modalTitle = document.getElementById('customizationModalTitle');
    const modalBody = document.getElementById('customizationModalBody');
    
    modalTitle.textContent = 'Customize: ' + serviceName;
    
    if (isCustomizable) {
        // Show advanced customization options
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
        // Show basic customization options
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
    
    const modal = new bootstrap.Modal(document.getElementById('customizationModal'));
    modal.show();
}

function saveCustomization() {
    if (!currentCustomizingService) return;
    
    const customization = {
        package: document.getElementById('customPackage') ? document.getElementById('customPackage').value : null,
        units: document.getElementById('customUnits') ? document.getElementById('customUnits').value : null,
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
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('customizationModal'));
    modal.hide();
    
    // Show success feedback
    const serviceCard = document.querySelector(`#service-${currentCustomizingService}`).closest('.service-option-card');
    const customizeBtn = serviceCard.querySelector('.btn-outline-primary');
    customizeBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Customized';
    customizeBtn.classList.remove('btn-outline-primary');
    customizeBtn.classList.add('btn-success');
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

// Form submission
// document.getElementById('bookingForm').addEventListener('submit', function(e) {
//     e.preventDefault();
    
//     if (!validateStep(5)) {
//         return;
//     }
    
//     const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
//     loadingModal.show();
    
//     const formData = new FormData(this);
    
//     fetch('process_booking.php', {
//         method: 'POST',
//         body: formData
//     })
//     .then(response => response.json())
//     .then(data => {
//         loadingModal.hide();
        
//         if (data.success) {
//             alert('Booking submitted successfully! Your reference number is: ' + data.booking_reference);
//             window.location.href = 'booking_success.php?reference=' + data.booking_reference;
//         } else {
//             alert('Error: ' + data.message);
//         }
//     })
//     .catch(error => {
//         loadingModal.hide();
//         alert('An error occurred while processing your booking. Please try again.');
//         console.error('Error:', error);
//     });
// });
</script>

<?php include __DIR__."/components/footer.php" ?>