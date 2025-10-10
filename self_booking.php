<?php 
session_start();
include __DIR__."/components/header.php"; 
?>

<?php
// Add this at the top of self_booking.php after session_start()
$detailedServices = [
    [
        'id' => 'catering',
        'name' => 'Catering Service',
        'description' => 'Professional food and beverage service for your event',
        'price_range' => '₱15,000 - ₱80,000',
        'details' => [
            'Buffet Style (₱15,000-₱35,000)',
            'Plated Service (₱25,000-₱50,000)', 
            'Cocktail Setup (₱20,000-₱40,000)',
            'Dessert & Coffee Station (₱8,000-₱15,000)'
        ],
        'features' => ['Menu Planning', 'Professional Staff', 'Food Safety Certified', 'Setup & Cleanup'],
        'popular_for' => ['Weddings', 'Corporate Events', 'Birthdays'],
        'image' => 'assets/services/catering.jpg'
    ],
    [
        'id' => 'decoration',
        'name' => 'Decoration Setup',
        'description' => 'Transform your venue with beautiful decor and themes',
        'price_range' => '₱10,000 - ₱50,000',
        'details' => [
            'Classic Theme (₱10,000-₱25,000)',
            'Rustic Theme (₱15,000-₱30,000)',
            'Modern Theme (₱20,000-₱40,000)',
            'Floral Arrangements (₱5,000-₱20,000)'
        ],
        'features' => ['Theme Consultation', 'Setup & Teardown', 'Custom Designs', 'Fresh Flowers'],
        'popular_for' => ['Weddings', 'Debut', 'Anniversaries'],
        'image' => 'assets/services/decoration.jpg'
    ],
    [
        'id' => 'photography',
        'name' => 'Photography & Videography',
        'description' => 'Capture your special moments professionally',
        'price_range' => '₱12,000 - ₱60,000',
        'details' => [
            'Basic Package (₱12,000-₱25,000)',
            'Standard Package (₱20,000-₱35,000)',
            'Premium Package (₱30,000-₱50,000)',
            'Drone Coverage (₱8,000-₱15,000)'
        ],
        'features' => ['Professional Equipment', 'Edited Photos/Videos', 'Online Gallery', 'Print Options'],
        'popular_for' => ['All Event Types'],
        'image' => 'assets/services/photography.jpg'
    ],
    [
        'id' => 'sound_light',
        'name' => 'Sound System & Lights',
        'description' => 'Professional audio and lighting for perfect ambiance',
        'price_range' => '₱8,000 - ₱35,000',
        'details' => [
            'Basic Sound Setup (₱8,000-₱15,000)',
            'Full DJ Equipment (₱15,000-₱25,000)',
            'Stage Lighting (₱10,000-₱20,000)',
            'LED Wall (₱20,000-₱35,000)'
        ],
        'features' => ['Professional Audio', 'Lighting Effects', 'Technician Support', 'Backup Equipment'],
        'popular_for' => ['Parties', 'Corporate Events', 'Weddings'],
        'image' => 'assets/services/sound.jpg'
    ],
    [
        'id' => 'entertainment',
        'name' => 'Entertainment',
        'description' => 'Keep your guests entertained throughout the event',
        'price_range' => '₱10,000 - ₱100,000',
        'details' => [
            'Live Band (₱25,000-₱100,000)',
            'DJ Services (₱10,000-₱30,000)',
            'Magician (₱15,000-₱25,000)',
            'Photo Booth (₱8,000-₱15,000)'
        ],
        'features' => ['Professional Performers', 'Equipment Included', 'Music Coordination', 'Interactive Elements'],
        'popular_for' => ['Birthdays', 'Corporate Parties', 'Weddings'],
        'image' => 'assets/services/entertainment.jpg'
    ],
    [
        'id' => 'coordination',
        'name' => 'Event Coordination',
        'description' => 'Professional event management and coordination',
        'price_range' => '₱15,000 - ₱50,000',
        'details' => [
            'On-the-day Coordination (₱15,000-₱25,000)',
            'Partial Planning (₱25,000-₱35,000)',
            'Full Planning (₱35,000-₱50,000)'
        ],
        'features' => ['Timeline Management', 'Vendor Coordination', 'Problem Solving', 'Day-of Supervision'],
        'popular_for' => ['Weddings', 'Large Events', 'Corporate Functions'],
        'image' => 'assets/services/coordination.jpg'
    ],
    [
        'id' => 'invitations',
        'name' => 'Invitation Design & Printing',
        'description' => 'Beautiful custom invitations for your guests',
        'price_range' => '₱5,000 - ₱20,000',
        'details' => [
            'Digital Invitations (₱5,000-₱8,000)',
            'Printed Invitations (₱8,000-₱15,000)',
            'Luxury Suite (₱15,000-₱20,000)'
        ],
        'features' => ['Custom Design', 'Multiple Revisions', 'RSVP Management', 'Quality Printing'],
        'popular_for' => ['Weddings', 'Formal Events', 'Corporate Launches'],
        'image' => 'assets/services/invitations.jpg'
    ],
    [
        'id' => 'souvenirs',
        'name' => 'Souvenirs & Giveaways',
        'description' => 'Memorable tokens for your guests',
        'price_range' => '₱3,000 - ₱25,000',
        'details' => [
            'Basic Souvenirs (₱3,000-₱8,000)',
            'Customized Items (₱8,000-₱15,000)',
            'Premium Giveaways (₱15,000-₱25,000)'
        ],
        'features' => ['Custom Branding', 'Various Options', 'Gift Wrapping', 'Delivery Setup'],
        'popular_for' => ['All Event Types'],
        'image' => 'assets/services/souvenirs.jpg'
    ]
];
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
            <form id="bookingForm" method="POST" action="process_booking.php" enctype="multipart/form-data">
                <input type="hidden" name="booking_type" value="self">
                <input type="hidden" name="booking_reference" id="bookingReference">
                <input type="hidden" name="venue_type" id="venueType">
                <input type="hidden" name="venue_id" id="venueId">
                <input type="hidden" name="event_location" id="eventLocation">
                <input type="hidden" name="full_address" id="fullAddress">
                <input type="hidden" name="alternate_phone" id="alternatePhone">
                <input type="hidden" name="backup_email" id="backupEmail">
                
                <!-- Step 1: Choose Package -->
                <div class="step active" id="step-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Step 1: Choose Your Event Package</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package1" value="Wedding Event Package" data-price="108190" required>
                                                <label class="form-check-label w-100" for="package1">
                                                    <h5>Wedding Event Package</h5>
                                                    <p class="text-muted mb-2">Complete wedding planning with catering, floral design, and full coordination.</p>
                                                    <h5 class="text-primary">₱108,190</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package2" value="Birthday Celebration Package" data-price="50000" required>
                                                <label class="form-check-label w-100" for="package2">
                                                    <h5>Birthday Celebration Package</h5>
                                                    <p class="text-muted mb-2">Perfect for kids or adults, with cake, decorations, and entertainment add-ons.</p>
                                                    <h5 class="text-primary">₱50,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package3" value="Corporate Event Package" data-price="140500" required>
                                                <label class="form-check-label w-100" for="package3">
                                                    <h5>Corporate Event Package</h5>
                                                    <p class="text-muted mb-2">Professional setup for seminars, product launches, or company parties.</p>
                                                    <h5 class="text-primary">₱140,500</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package4" value="Debut Package" data-price="95000" required>
                                                <label class="form-check-label w-100" for="package4">
                                                    <h5>Debut Package</h5>
                                                    <p class="text-muted mb-2">Includes 18 roses & candles setup, photography, and custom stage backdrop.</p>
                                                    <h5 class="text-primary">₱95,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package5" value="Christening Package" data-price="45000" required>
                                                <label class="form-check-label w-100" for="package5">
                                                    <h5>Christening Package</h5>
                                                    <p class="text-muted mb-2">Includes catering for 50 guests, souvenirs, and floral setup.</p>
                                                    <h5 class="text-primary">₱45,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package6" value="Anniversary Celebration" data-price="75000" required>
                                                <label class="form-check-label w-100" for="package6">
                                                    <h5>Anniversary Celebration</h5>
                                                    <p class="text-muted mb-2">Romantic dinner setting with live music and custom themes.</p>
                                                    <h5 class="text-primary">₱75,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package7" value="Holiday Party Package" data-price="85000" required>
                                                <label class="form-check-label w-100" for="package7">
                                                    <h5>Holiday Party Package</h5>
                                                    <p class="text-muted mb-2">Christmas or New Year party with buffet and entertainment options.</p>
                                                    <h5 class="text-primary">₱85,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package8" value="Graduation Party Package" data-price="35000" required>
                                                <label class="form-check-label w-100" for="package8">
                                                    <h5>Graduation Party Package</h5>
                                                    <p class="text-muted mb-2">Includes simple catering, tarpaulin, and sound system.</p>
                                                    <h5 class="text-primary">₱35,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package9" value="Engagement Party Package" data-price="65000" required>
                                                <label class="form-check-label w-100" for="package9">
                                                    <h5>Engagement Party Package</h5>
                                                    <p class="text-muted mb-2">Intimate gathering with floral arrangements and photography.</p>
                                                    <h5 class="text-primary">₱65,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="package" id="package10" value="Reunion Event Package" data-price="55000" required>
                                                <label class="form-check-label w-100" for="package10">
                                                    <h5>Reunion Event Package</h5>
                                                    <p class="text-muted mb-2">Perfect for family or class reunions with buffet and photo booth.</p>
                                                    <h5 class="text-primary">₱55,000</h5>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                    <!-- Venues will be loaded here -->
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
                                <?php foreach ($detailedServices as $service): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card service-option-card h-100">
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" value="<?php echo $service['name']; ?>" id="service-<?php echo $service['id']; ?>">
                                                <label class="form-check-label fw-bold" for="service-<?php echo $service['id']; ?>">
                                                    <?php echo $service['name']; ?>
                                                </label>
                                            </div>
                                            <p class="text-muted small mb-2"><?php echo $service['description']; ?></p>
                                            <p class="text-primary fw-bold mb-3"><?php echo $service['price_range']; ?></p>
                                            
                                            <div class="service-details">
                                                <h6 class="mb-2">Options:</h6>
                                                <ul class="small text-muted mb-3">
                                                    <?php foreach ($service['details'] as $detail): ?>
                                                    <li><?php echo $detail; ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                
                                                <h6 class="mb-2">Includes:</h6>
                                                <div class="d-flex flex-wrap gap-1 mb-3">
                                                    <?php foreach ($service['features'] as $feature): ?>
                                                    <span class="badge bg-light text-dark"><?php echo $feature; ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <h6 class="mb-2">Popular For:</h6>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($service['popular_for'] as $event): ?>
                                                    <span class="badge bg-primary"><?php echo $event; ?></span>
                                                    <?php endforeach; ?>
                                                </div>
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
                                        <label for="full_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_number" class="form-label">Contact Number *</label>
                                        <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alternate_contact" class="form-label">Alternate Contact Number</label>
                                        <input type="tel" class="form-control" id="alternate_contact" name="alternate_contact">
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
                                <div class="col-md-12">
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
                            <h4 class="mb-0">Step 5: Payment Information</h4>
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
                                    <h4 class="text-primary" id="totalAmount">Total: ₱0.00</h4>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-4">
                                <h5 class="mb-3">Select Payment Method</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card payment-option">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="payment1" value="GCash" required>
                                                    <label class="form-check-label w-100" for="payment1">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-phone me-3 fs-4"></i>
                                                            <div>
                                                                <h6 class="mb-1">GCash</h6>
                                                                <small class="text-muted">Pay using GCash mobile app</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card payment-option">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="payment2" value="Bank Transfer" required>
                                                    <label class="form-check-label w-100" for="payment2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-bank me-3 fs-4"></i>
                                                            <div>
                                                                <h6 class="mb-1">Bank Transfer</h6>
                                                                <small class="text-muted">Transfer to our bank account</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card payment-option">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="payment3" value="PayPal" required>
                                                    <label class="form-check-label w-100" for="payment3">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-paypal me-3 fs-4"></i>
                                                            <div>
                                                                <h6 class="mb-1">PayPal</h6>
                                                                <small class="text-muted">Pay using PayPal</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card payment-option">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="payment4" value="Installment" required>
                                                    <label class="form-check-label w-100" for="payment4">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-calendar-check me-3 fs-4"></i>
                                                            <div>
                                                                <h6 class="mb-1">Installment Plan</h6>
                                                                <small class="text-muted">Pay in multiple installments</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                <button type="submit" class="btn btn-success" id="submitBooking">Complete Booking</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
// Generate booking reference on page load
document.addEventListener('DOMContentLoaded', function() {
    generateBookingReference();
    updateOrderSummary();
    
    // Add event listeners for dynamic updates
    document.querySelectorAll('input[name="package"]').forEach(radio => {
        radio.addEventListener('change', updateOrderSummary);
    });
    
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateOrderSummary);
    });
    
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const receiptUpload = document.getElementById('receiptUpload');
            const methodsRequiringReceipt = ['GCash', 'Bank Transfer', 'PayPal', 'Installment'];
            if (methodsRequiringReceipt.includes(this.value)) {
                receiptUpload.style.display = 'block';
                document.getElementById('receipt').required = true;
            } else {
                receiptUpload.style.display = 'none';
                document.getElementById('receipt').required = false;
            }
        });
    });
});

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
                                        <p class="text-primary fw-bold">₱${venue.price.toLocaleString()}</p>
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

function nextStep(step) {
    // Validate current step before proceeding
    if (!validateStep(step - 1)) {
        return;
    }
    
    // Hide all steps
    document.querySelectorAll('.step').forEach(stepEl => {
        stepEl.classList.remove('active');
    });
    
    // Show target step
    document.getElementById('step-' + step).classList.add('active');
    
    // Update progress bar
    const progress = (step - 1) * 25;
    document.getElementById('progress-bar').style.width = progress + '%';
    
    // Update step indicators
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        if (index < step) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
    
    // Update order summary when reaching payment step
    if (step === 5) {
        updateOrderSummary();
    }
}

function prevStep(step) {
    // Hide all steps
    document.querySelectorAll('.step').forEach(stepEl => {
        stepEl.classList.remove('active');
    });
    
    // Show target step
    document.getElementById('step-' + step).classList.add('active');
    
    // Update progress bar
    const progress = (step - 1) * 25;
    document.getElementById('progress-bar').style.width = progress + '%';
    
    // Update step indicators
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
    
    // Step 1: Package selection
    if (step === 1) {
        const packageSelected = document.querySelector('input[name="package"]:checked');
        if (!packageSelected) {
            alert('Please select an event package to continue.');
            return false;
        }
    }
    
    // Step 2: Venue selection
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
            
            // Set the location and address for own venue
            document.getElementById('eventLocation').value = venueAddress;
            document.getElementById('fullAddress').value = `${venueAddress}, ${venueCity}, ${venuePostal}`;
        }
    }
    
    // Step 4: Contact information
    if (step === 4) {
        const requiredFields = ['full_name', 'email', 'contact_number', 'event_date', 'event_time'];
        for (let field of requiredFields) {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                alert('Please complete all required fields to continue.');
                element.focus();
                return false;
            }
        }
        
        // Validate email format
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address.');
            document.getElementById('email').focus();
            return false;
        }
        
        // Validate event date is not in the past
        const eventDate = new Date(document.getElementById('event_date').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (eventDate < today) {
            alert('Event date cannot be in the past.');
            document.getElementById('event_date').focus();
            return false;
        }
        
        // Set alternate contact and backup email in hidden fields
        document.getElementById('alternatePhone').value = document.getElementById('alternate_contact').value;
        document.getElementById('backupEmail').value = document.getElementById('backup_email').value;
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
        // You would need to add data-price attributes to your service checkboxes
        const servicePrice = getServicePrice(serviceName);
        total += servicePrice;
        summaryHTML += `
            <tr>
                <td><strong>Service</strong></td>
                <td>${serviceName}</td>
                <td class="text-end">₱${servicePrice.toLocaleString()}</td>
            </tr>
        `;
    });
    
    // Venue (if rental)
    const venueType = document.getElementById('venueType').value;
    if (venueType === 'rental') {
        const selectedVenue = document.querySelector('input[name="selected_venue"]:checked');
        if (selectedVenue) {
            // Venue price would need to be included in the venue data
            // For now, we'll assume it's included in the package
        }
    }
    
    summaryContainer.innerHTML = summaryHTML || '<tr><td colspan="3" class="text-center text-muted">No items selected</td></tr>';
    document.getElementById('totalAmount').textContent = `Total: ₱${total.toLocaleString()}`;
}

function getServicePrice(serviceName) {
    const servicePrices = {
        'Catering Service': 15000,
        'Decoration Setup': 10000,
        'Photography & Videography': 12000,
        'Sound System & Lights': 8000,
        'Entertainment': 10000,
        'Event Coordination': 15000,
        'Invitation Design & Printing': 5000,
        'Souvenirs & Giveaways': 3000
    };
    
    return servicePrices[serviceName] || 0;
}

// Form submission
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateStep(5)) {
        return;
    }
    
    // Show loading modal
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    const formData = new FormData(this);
    
    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loadingModal.hide();
        
        if (data.success) {
            // Show success message and redirect
            alert('Booking submitted successfully! Your reference number is: ' + data.booking_reference);
            window.location.href = 'booking_success.php?reference=' + data.booking_reference;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        loadingModal.hide();
        alert('An error occurred while processing your booking. Please try again.');
        console.error('Error:', error);
    });
});
</script>

<?php include __DIR__."/components/footer.php" ?>