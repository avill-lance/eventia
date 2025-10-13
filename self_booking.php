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
                                            
                                            <!-- Customization Button -->
                                            <div class="customization-options mt-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary customize-service-btn" 
                                                        data-service-id="<?php echo $service['service_id']; ?>"
                                                        data-service-name="<?php echo htmlspecialchars($service['service_name']); ?>"
                                                        data-customizable="<?php echo $service['customizable'] ? 'true' : 'false'; ?>">
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
                                        <tbody id="orderSummary" name="orderSummary">
                                            <!-- Order summary will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end">
                                    <h4 class="text-primary" id="amount">Total: ₱0.00</h4>
                                </div>
                            </div>

                            <!-- Payment Instructions -->
                            <div class="alert alert-info mb-4">
                                <h6><i class="bi bi-info-circle me-2"></i>Payment Instructions</h6>
                                <p class="mb-2">You will be redirected to PayMongo to complete your payment securely.</p>
                                <p class="mb-0"><strong>Supported Payment Methods:</strong> Credit/Debit Card, GCash, GrabPay</p>
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
                <button type="button" class="btn btn-primary" id="saveCustomizationBtn">Save Customization</button>
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

<script src="js/jquery-3.7.1.js"></script>
<script src="js/sweetalert2@11.js"></script>
<script src="js/self_booking.js"></script>
