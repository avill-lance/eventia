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

// Fetch services from database
$services = [];
$services_result = $conn->query("SELECT service_id, service_name, service_description, base_price, category, customizable FROM tbl_services WHERE status = 'active'");
while ($row = $services_result->fetch_assoc()) {
    $services[] = $row;
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
                        <div class="text-primary fw-bold">EVT-<span id="refNumber"></span></div>
                    </div>
                </div>
            </div>
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

<!-- Success Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="service-icon mb-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h3 class="mb-3">Booking Confirmed!</h3>
                <p class="text-muted mb-4">Your booking is now confirmed under Reference Code <strong class="text-primary">EVT-<span id="finalRefNumber"></span></strong></p>
                <p class="mb-4">You'll receive an email summary shortly with all the details.</p>
                
                <div class="card text-start mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Booking Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="confirmation-details">
                            <div class="detail-row">
                                <span class="detail-label">Event Type:</span>
                                <span class="detail-value" id="finalEventType"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Venue:</span>
                                <span class="detail-value" id="finalVenue"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date:</span>
                                <span class="detail-value" id="finalDate"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Client:</span>
                                <span class="detail-value" id="finalClient"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment:</span>
                                <span class="detail-value" id="finalPayment"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary me-2" onclick="window.location.href='index.php'">Return to Home</button>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">Print Summary</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Booking data storage
    let bookingData = {
        eventType: '',
        venue: '',
        venueType: '',
        venueId: null,
        services: [],
        customizations: {},
        clientInfo: {},
        paymentMethod: '',
        reference: Math.floor(100000 + Math.random() * 900000)
    };

    // Current stage tracking
    let currentStage = 1;
    const totalStages = 6;
    let currentCustomizingService = null;

    // Set reference number
    document.getElementById('refNumber').textContent = bookingData.reference;
    document.getElementById('finalRefNumber').textContent = bookingData.reference;

    // Chat conversation flow
    const conversationFlow = {
        1: { // Stage 1 - Select Event Type
            question: "What type of event are you planning?",
            options: <?php echo json_encode(array_map(function($pkg) { return ['text' => $pkg['package_name'], 'value' => $pkg['package_name']]; }, $packages)); ?>,
            action: (value) => {
                bookingData.eventType = value;
                updateSummary('eventType', value);
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
            options: <?php echo json_encode(array_map(function($service) { return ['text' => $service['service_name'], 'value' => $service['service_id'], 'name' => $service['service_name']]; }, $services)); ?>,
            action: (values) => {
                bookingData.services = values;
                updateServicesSummary(values);
                addAdminMessage(`Excellent selections! I've added those services to your booking. ✨`);
                
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
                { text: "GCash", value: "GCash" },
                { text: "Bank Transfer", value: "Bank Transfer" },
                { text: "PayPal", value: "PayPal" },
                { text: "Installment Plan", value: "Installment" }
            ],
            action: (value) => {
                bookingData.paymentMethod = value;
                updateSummary('payment', value);
                showPaymentDetails(value);
            }
        }
    };

    // Initialize first stage
    setTimeout(() => startStage(1), 1000);

    function startStage(stage) {
        currentStage = stage;
        updateProgress();
        
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
            addAdminMessage(flow.question);
            
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
                    <input class="form-check-input service-checkbox" type="checkbox" value="${option.value}" id="opt_${option.value}">
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
                    callback(option.value);
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

                venues.forEach(venue => {
                    addAdminMessage(
                        `<strong>${venue.venue_name}</strong><br>
                        ${venue.venue_type} • Fits ${venue.capacity} guests<br>
                        <small class="text-muted">${venue.location}</small>`, 
                        'venue-option'
                    );
                });

                const venueOptions = venues.map(v => ({ 
                    text: v.venue_name, 
                    value: v.venue_id,
                    name: v.venue_name
                }));
                venueOptions.push({ text: "I have my own venue", value: "own_venue", name: "Own Venue" });

                setTimeout(() => {
                    showOptions(venueOptions, (value) => {
                        if (value === "own_venue") {
                            bookingData.venueType = 'own';
                            showCustomVenueInput();
                        } else {
                            bookingData.venueId = value;
                            const selectedVenue = venueOptions.find(v => v.value == value);
                            bookingData.venue = selectedVenue.name;
                            updateSummary('venue', selectedVenue.name);
                            addAdminMessage(`Excellent choice! ${selectedVenue.name} it is! 🏛️`);
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
        input.onkeypress = (e) => {
            if (e.key === 'Enter' && input.value.trim()) {
                const venue = input.value.trim();
                addUserMessage(venue);
                bookingData.venue = venue;
                updateSummary('venue', 'Own Venue: ' + venue);
                chatInput.style.display = 'none';
                input.value = '';
                input.onkeypress = null;
                addAdminMessage(`Got it! We'll set up at ${venue}. 📍`);
                setTimeout(() => startStage(3), 1000);
            }
        };
    }

    function showServiceCustomizations(serviceIds) {
        const servicesToCustomize = serviceIds.filter(id => {
            const service = <?php echo json_encode($services); ?>.find(s => s.service_id == id);
            return service && service.customizable;
        });

        if (servicesToCustomize.length === 0) {
            addAdminMessage("All services have been added! Let's move on to your information.");
            setTimeout(() => startStage(4), 1000);
            return;
        }

        addAdminMessage("Some of your selected services can be customized. Let me show you the options...");
        
        let currentServiceIndex = 0;
        
        function showNextCustomization() {
            if (currentServiceIndex >= servicesToCustomize.length) {
                addAdminMessage("All customizations have been set! Let's move on to your information.");
                setTimeout(() => startStage(4), 1000);
                return;
            }
            
            const serviceId = servicesToCustomize[currentServiceIndex];
            const service = <?php echo json_encode($services); ?>.find(s => s.service_id == serviceId);
            
            if (service) {
                showCustomizationModal(service);
            } else {
                currentServiceIndex++;
                showNextCustomization();
            }
        }
        
        function showCustomizationModal(service) {
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
            
            const modal = new bootstrap.Modal(document.getElementById('customizationModal'));
            modal.show();
            
            // Override the save button
            const originalSave = window.saveCustomization;
            window.saveCustomization = function() {
                const customization = {
                    package: document.getElementById('customPackage').value,
                    units: document.getElementById('customUnits').value,
                    requirements: document.getElementById('customRequirements').value,
                    notes: document.getElementById('customNotes').value
                };
                
                bookingData.customizations[service.service_id] = customization;
                addAdminMessage(`${service.service_name} customization saved! ✅`);
                
                modal.hide();
                currentServiceIndex++;
                
                // Restore original function
                window.saveCustomization = originalSave;
                
                setTimeout(showNextCustomization, 500);
            };
        }
        
        showNextCustomization();
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
        const guestCount = document.getElementById('guestCount').value;
        const instructions = document.getElementById('specialInstructions').value.trim();

        if (!name || !email || !phone || !date) {
            alert('Please fill in all required fields');
            return;
        }

        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }

        bookingData.clientInfo = { name, email, phone, date, guestCount, instructions };
        
        addUserMessage(`Name: ${name}\nEmail: ${email}\nPhone: ${phone}\nEvent Date: ${date}\nGuests: ${guestCount}`);
        
        updateSummary('client', `${name}<br><small>${email}<br>${phone}</small>`);
        updateSummary('date', date);
        if (instructions) {
            updateSummary('requests', instructions);
        }
        
        document.getElementById('chatOptions').innerHTML = '';
        
        addAdminMessage(`Thank you, ${name}! I have all your information. 👍`);
        setTimeout(() => startStage(5), 1000);
    }

    function showPaymentDetails(method) {
        let message = '';
        
        switch(method) {
            case 'GCash':
                message = `Please send your payment to:<br><strong>GCash Number:</strong> 0917-123-4567<br><strong>Account Name:</strong> Eventia Events Management<br><br>Upload your receipt and we'll confirm your booking! 💳`;
                break;
            case 'Bank Transfer':
                message = `You can transfer to any of these accounts:<br><strong>BDO:</strong> 1234-5678-9012<br><strong>BPI:</strong> 9876-5432-1098<br><strong>Account Name:</strong> Eventia Events Management 🏦`;
                break;
            case 'PayPal':
                message = `Send your payment to:<br><strong>PayPal Email:</strong> payments@eventia.com<br><br>Perfect for digital transactions! 💻`;
                break;
            case 'Installment':
                message = `Great! Here are the terms:<br>• 50% down payment upon confirmation<br>• 50% balance on event date<br><br>Send your down payment and we'll lock in your booking! 📊`;
                break;
        }
        
        addAdminMessage(message);
        
        setTimeout(() => {
            addAdminMessage("Your booking is now being processed. Let me finalize everything...");
            setTimeout(() => submitBooking(), 2000);
        }, 2000);
    }

    function submitBooking() {
        // Prepare form data with all required fields
        const formData = new FormData();
        formData.append('booking_type', 'guided');
        formData.append('booking_reference', 'EVT-' + bookingData.reference);
        formData.append('package', bookingData.eventType);
        formData.append('venue_type', bookingData.venueType);
        
        if (bookingData.venueId) {
            formData.append('venue_id', bookingData.venueId);
        }
        
        formData.append('venue_address', bookingData.venue);
        formData.append('event_location', bookingData.venue);
        formData.append('full_address', bookingData.venue);
        formData.append('contact_name', bookingData.clientInfo.name);
        formData.append('contact_email', bookingData.clientInfo.email);
        formData.append('contact_phone', bookingData.clientInfo.phone);
        formData.append('alternate_phone', bookingData.clientInfo.phone);
        formData.append('backup_email', bookingData.clientInfo.email);
        formData.append('event_date', bookingData.clientInfo.date);
        formData.append('event_time', 'To be determined');
        formData.append('guest_count', bookingData.clientInfo.guestCount || 50);
        formData.append('preferred_contact', 'Any');
        formData.append('special_instructions', bookingData.clientInfo.instructions || '');
        formData.append('payment_method', bookingData.paymentMethod);
        formData.append('company_name', '');

        // Add services
        bookingData.services.forEach(serviceId => {
            const service = <?php echo json_encode($services); ?>.find(s => s.service_id == serviceId);
            if (service) {
                formData.append('services[]', service.service_name);
            }
        });

        // Add customizations
        Object.keys(bookingData.customizations).forEach(serviceId => {
            formData.append(`customization[${serviceId}]`, JSON.stringify(bookingData.customizations[serviceId]));
        });

        // Submit to server
        fetch('process_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                confirmBooking();
            } else {
                addAdminMessage('Sorry, there was an error processing your booking. Please try again or contact us directly.');
                console.error('Booking error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            addAdminMessage('Sorry, there was an error processing your booking. Please try again or contact us directly.');
        });
    }

    function confirmBooking() {
        currentStage = 6;
        updateProgress();
        document.getElementById('currentStage').textContent = 'Stage 6: Confirmation';
        
        addAdminMessage(`🎉 <strong>Congratulations!</strong> Your booking is now confirmed under Reference Code <strong class="text-primary">EVT-${bookingData.reference}</strong><br><br>You'll receive an email summary shortly. Thank you for choosing Eventia!`);
        
        // Update final modal
        document.getElementById('finalEventType').textContent = bookingData.eventType;
        document.getElementById('finalVenue').textContent = bookingData.venue;
        document.getElementById('finalDate').textContent = bookingData.clientInfo.date || '-';
        document.getElementById('finalClient').textContent = bookingData.clientInfo.name || '-';
        document.getElementById('finalPayment').textContent = bookingData.paymentMethod;
        
        setTimeout(() => {
            const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            modal.show();
        }, 2000);
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
        serviceIds.forEach(serviceId => {
            const service = <?php echo json_encode($services); ?>.find(s => s.service_id == serviceId);
            if (service) {
                const li = document.createElement('li');
                li.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> ${service.service_name}`;
                container.appendChild(li);
            }
        });
    }

    // Default save customization function
    function saveCustomization() {
        // This function is overridden during service customization flow
    }
</script>

<?php include __DIR__."/components/footer.php" ?>