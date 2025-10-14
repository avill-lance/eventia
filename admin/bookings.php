<?php 
include __DIR__ . '/admin-components/admin-header.php';
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Fetch bookings data
$bookings = [];
$bookingStats = [];
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';
$dateFilter = $_GET['date'] ?? '';

try {
    // Build query with filters
    $query = "SELECT b.*, 
                     u.first_name, 
                     u.last_name, 
                     u.email as user_email,
                     u.phone as user_phone,
                     v.venue_name,
                     p.package_name,
                     COUNT(bs.booking_service_id) as service_count
              FROM tbl_bookings b
              LEFT JOIN tbl_users u ON b.user_id = u.user_id
              LEFT JOIN tbl_venues v ON b.venue_id = v.venue_id
              LEFT JOIN tbl_packages p ON b.package_id = p.package_id
              LEFT JOIN tbl_booking_services bs ON b.booking_id = bs.booking_id";
    
    $whereClauses = [];
    $params = [];
    $types = '';
    
    if (!empty($searchTerm)) {
        $whereClauses[] = "(b.booking_reference LIKE ? OR b.contact_name LIKE ? OR b.contact_email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $types .= 'sssss';
    }
    
    if ($statusFilter !== 'all') {
        $whereClauses[] = "b.booking_status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
    
    if ($typeFilter !== 'all') {
        $whereClauses[] = "b.booking_type = ?";
        $params[] = $typeFilter;
        $types .= 's';
    }
    
    if (!empty($dateFilter)) {
        $whereClauses[] = "b.event_date = ?";
        $params[] = $dateFilter;
        $types .= 's';
    }
    
    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    $query .= " GROUP BY b.booking_id ORDER BY b.created_at DESC";
    
    // Prepare and execute query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Fetch booking statistics
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings");
    $bookingStats['totalBookings'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings WHERE booking_status = 'confirmed'");
    $bookingStats['confirmedBookings'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings WHERE booking_status = 'pending'");
    $bookingStats['pendingBookings'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings WHERE payment_status = 'paid'");
    $bookingStats['paidBookings'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT SUM(total_amount) as total FROM tbl_bookings WHERE payment_status = 'paid'");
    $bookingStats['totalRevenue'] = $result->fetch_assoc()['total'] ?? 0;
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings WHERE event_date >= CURDATE()");
    $bookingStats['upcomingEvents'] = $result->fetch_assoc()['total'];
    
} catch (Exception $e) {
    $error = "Error loading bookings data: " . $e->getMessage();
}

// Fetch venues and packages for filters
$venues = $conn->query("SELECT venue_id, venue_name FROM tbl_venues WHERE status = 'available'")->fetch_all(MYSQLI_ASSOC);
$packages = $conn->query("SELECT package_id, package_name FROM tbl_packages WHERE status = 'active'")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!-- BOOKINGS MANAGEMENT -->
<section id="view-bookings" class="view">
    <div class="page-header">
        <h2>Bookings Management</h2>
        <div class="header-actions">
            <button class="btn primary" onclick="showBookingEditor()">
                <span>+ Add Booking</span>
            </button>
            <button class="btn" onclick="exportBookings()">
                <span>📊 Export</span>
            </button>
        </div>
    </div>
    
    <!-- Booking Statistics Cards -->
    <div class="cards">
        <div class="card">
            <h3>Total Bookings</h3>
            <div class="big"><?php echo htmlspecialchars($bookingStats['totalBookings'] ?? 0); ?></div>
            <div class="muted">All bookings</div>
        </div>
        <div class="card">
            <h3>Confirmed</h3>
            <div class="big"><?php echo htmlspecialchars($bookingStats['confirmedBookings'] ?? 0); ?></div>
            <div class="muted">Active events</div>
        </div>
        <div class="card">
            <h3>Pending</h3>
            <div class="big"><?php echo htmlspecialchars($bookingStats['pendingBookings'] ?? 0); ?></div>
            <div class="muted">Awaiting confirmation</div>
        </div>
        <div class="card">
            <h3>Revenue</h3>
            <div class="big">₱<?php echo number_format($bookingStats['totalRevenue'] ?? 0, 2); ?></div>
            <div class="muted">Total paid</div>
        </div>
        <div class="card">
            <h3>Upcoming</h3>
            <div class="big"><?php echo htmlspecialchars($bookingStats['upcomingEvents'] ?? 0); ?></div>
            <div class="muted">Future events</div>
        </div>
    </div>
    
    <!-- Filters and Search -->
    <div class="card" style="margin-top: 16px;">
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="search-field">
                    <input type="text" name="search" placeholder="Search bookings..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="btn">🔍</button>
                </div>
                
                <div class="filter-controls">
                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    
                    <select name="type" onchange="this.form.submit()">
                        <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="self" <?php echo $typeFilter === 'self' ? 'selected' : ''; ?>>Self Service</option>
                        <option value="guided" <?php echo $typeFilter === 'guided' ? 'selected' : ''; ?>>Guided</option>
                    </select>
                    
                    <input type="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>" 
                           onchange="this.form.submit()" placeholder="Event Date">
                    
                    <button type="button" class="btn" onclick="resetFilters()">Reset</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <div class="card" style="margin-top: 16px;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Booking Ref</th>
                        <th>Event Details</th>
                        <th>Customer</th>
                        <th>Package & Venue</th>
                        <th>Financials</th>
                        <th>Status</th>
                        <th>Event Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($booking['booking_reference']); ?></strong>
                                    <div class="muted" style="font-size: 0.75rem;">
                                        <?php echo date('M j, Y', strtotime($booking['created_at'])); ?>
                                    </div>
                                    <div class="badge badge-<?php echo $booking['booking_type'] === 'guided' ? 'warning' : 'info'; ?>" style="margin-top: 4px;">
                                        <?php echo ucfirst($booking['booking_type']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div><strong><?php echo htmlspecialchars($booking['event_type']); ?></strong></div>
                                    <div class="muted" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($booking['event_time']); ?>
                                    </div>
                                    <div class="muted" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($booking['guest_count'] ?? 0); ?> guests
                                    </div>
                                </td>
                                <td>
                                    <?php if ($booking['first_name']): ?>
                                        <div><strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong></div>
                                        <div class="muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($booking['user_email']); ?></div>
                                        <div class="muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($booking['user_phone'] ?? ''); ?></div>
                                    <?php else: ?>
                                        <div><strong><?php echo htmlspecialchars($booking['contact_name']); ?></strong></div>
                                        <div class="muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($booking['contact_email']); ?></div>
                                        <div class="muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($booking['contact_phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><strong><?php echo htmlspecialchars($booking['package_name'] ?? 'Custom'); ?></strong></div>
                                    <div class="muted" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($booking['venue_name'] ?? $booking['venue_type']); ?>
                                    </div>
                                    <div class="muted" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($booking['service_count']); ?> services
                                    </div>
                                </td>
                                <td>
                                    <div><strong>₱<?php echo number_format($booking['total_amount'], 2); ?></strong></div>
                                    <div class="muted" style="font-size: 0.75rem;">
                                        <?php echo ucfirst($booking['payment_method']); ?>
                                    </div>
                                    <div class="badge badge-<?php 
                                        echo $booking['payment_status'] === 'paid' ? 'success' : 
                                             ($booking['payment_status'] === 'partial' ? 'warning' : 
                                             ($booking['payment_status'] === 'cancelled' ? 'secondary' : 'info')); 
                                    ?>" style="margin-top: 4px;">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $booking['booking_status'] === 'confirmed' ? 'success' : 
                                             ($booking['booking_status'] === 'pending' ? 'warning' : 
                                             ($booking['booking_status'] === 'cancelled' ? 'secondary' : 'info')); 
                                    ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="muted" style="font-size: 0.75rem;">
                                        <?php echo date('M j, Y', strtotime($booking['event_date'])); ?>
                                    </div>
                                    <?php 
                                    $eventDate = strtotime($booking['event_date']);
                                    $today = strtotime('today');
                                    $daysDiff = ($eventDate - $today) / (60 * 60 * 24);
                                    
                                    if ($daysDiff < 0 && $booking['booking_status'] !== 'completed' && $booking['booking_status'] !== 'cancelled'): ?>
                                        <div class="badge badge-secondary" style="margin-top: 4px;">Past Due</div>
                                    <?php elseif ($daysDiff >= 0 && $daysDiff <= 7): ?>
                                        <div class="badge badge-warning" style="margin-top: 4px;">
                                            In <?php echo $daysDiff; ?> days
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" title="View Details" onclick="viewBookingDetails(<?php echo $booking['booking_id']; ?>)">
                                            👁️
                                        </button>
                                        <button class="btn-icon" title="Edit" onclick="editBooking(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                            ✏️
                                        </button>
                                        <button class="btn-icon" title="Update Status" onclick="showStatusModal(<?php echo $booking['booking_id']; ?>, '<?php echo $booking['booking_status']; ?>', '<?php echo $booking['payment_status']; ?>')">
                                            🔄
                                        </button>
                                        <button class="btn-icon" title="Send Reminder" onclick="sendReminder(<?php echo $booking['booking_id']; ?>)">
                                            📧
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;" class="muted">
                                No bookings found matching your criteria
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination" style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div class="muted" style="font-size: 0.875rem;">
                Showing <?php echo count($bookings); ?> bookings
            </div>
            <div class="pagination-controls">
                <button class="btn" disabled>Previous</button>
                <span style="margin: 0 12px;">Page 1 of 1</span>
                <button class="btn" disabled>Next</button>
            </div>
        </div>
    </div>
</section>

<!-- MODAL: Booking Editor -->
<dialog id="bookingEditorModal">
    <div class="modal-header">
        <strong id="bookingEditorTitle">Add New Booking</strong>
        <button class="btn" onclick="closeBookingEditor()">✕</button>
    </div>
    <div class="modal-body">
        <form id="bookingEditorForm" enctype="multipart/form-data">
            <input type="hidden" id="bookingId" name="booking_id">
            
            <div class="form-grid">
                <div class="field">
                    <label for="bookingReference">Booking Reference *</label>
                    <input type="text" id="bookingReference" name="booking_reference" required 
                           value="EVT-<?php echo time(); ?>" readonly>
                </div>
                <div class="field">
                    <label for="bookingType">Booking Type *</label>
                    <select id="bookingType" name="booking_type" required>
                        <option value="self">Self Service</option>
                        <option value="guided">Guided</option>
                    </select>
                </div>
                <div class="field">
                    <label for="eventType">Event Type *</label>
                    <input type="text" id="eventType" name="event_type" required 
                           placeholder="e.g., Wedding, Birthday, Corporate">
                </div>
                <div class="field">
                    <label for="eventDate">Event Date *</label>
                    <input type="date" id="eventDate" name="event_date" required>
                </div>
                <div class="field">
                    <label for="eventTime">Event Time</label>
                    <input type="text" id="eventTime" name="event_time" placeholder="e.g., 2:00 PM">
                </div>
                <div class="field">
                    <label for="guestCount">Guest Count</label>
                    <input type="number" id="guestCount" name="guest_count" min="1" value="50">
                </div>
                <div class="field">
                    <label for="venueType">Venue Type</label>
                    <select id="venueType" name="venue_type">
                        <option value="own">Own Venue</option>
                        <option value="rental">Rental</option>
                    </select>
                </div>
                <div class="field">
                    <label for="venueSelect">Venue</label>
                    <select id="venueSelect" name="venue_id">
                        <option value="">Select Venue</option>
                        <?php foreach ($venues as $venue): ?>
                            <option value="<?php echo $venue['venue_id']; ?>">
                                <?php echo htmlspecialchars($venue['venue_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="packageSelect">Package</label>
                    <select id="packageSelect" name="package_id">
                        <option value="">Select Package</option>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['package_id']; ?>">
                                <?php echo htmlspecialchars($package['package_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="eventLocation">Event Location</label>
                    <input type="text" id="eventLocation" name="event_location" 
                           placeholder="Full event address">
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="specialInstructions">Special Instructions</label>
                    <textarea id="specialInstructions" name="special_instructions" 
                              rows="3" placeholder="Any special requirements or notes"></textarea>
                </div>
            </div>
            
            <h3 style="margin: 20px 0 10px 0;">Customer Information</h3>
            <div class="form-grid">
                <div class="field">
                    <label for="contactName">Contact Name *</label>
                    <input type="text" id="contactName" name="contact_name" required>
                </div>
                <div class="field">
                    <label for="contactEmail">Contact Email *</label>
                    <input type="email" id="contactEmail" name="contact_email" required>
                </div>
                <div class="field">
                    <label for="contactPhone">Contact Phone *</label>
                    <input type="tel" id="contactPhone" name="contact_phone" required>
                </div>
                <div class="field">
                    <label for="alternatePhone">Alternate Phone</label>
                    <input type="tel" id="alternatePhone" name="alternate_phone">
                </div>
                <div class="field">
                    <label for="companyName">Company Name</label>
                    <input type="text" id="companyName" name="company_name">
                </div>
                <div class="field">
                    <label for="preferredContact">Preferred Contact</label>
                    <select id="preferredContact" name="preferred_contact">
                        <option value="Any">Any</option>
                        <option value="Email">Email</option>
                        <option value="Phone">Phone</option>
                        <option value="SMS">SMS</option>
                    </select>
                </div>
            </div>
            
            <h3 style="margin: 20px 0 10px 0;">Payment Information</h3>
            <div class="form-grid">
                <div class="field">
                    <label for="totalAmount">Total Amount (₱)</label>
                    <input type="number" id="totalAmount" name="total_amount" step="0.01" min="0" value="0">
                </div>
                <div class="field">
                    <label for="paymentMethod">Payment Method</label>
                    <select id="paymentMethod" name="payment_method">
                        <option value="GCash">GCash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Installment">Installment</option>
                    </select>
                </div>
                <div class="field">
                    <label for="paymentStatus">Payment Status</label>
                    <select id="paymentStatus" name="payment_status">
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="field">
                    <label for="bookingStatus">Booking Status</label>
                    <select id="bookingStatus" name="booking_status">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
                <button class="btn" type="button" onclick="closeBookingEditor()">Cancel</button>
                <button class="btn primary" type="submit" id="bookingSaveBtn">Save Booking</button>
            </div>
        </form>
    </div>
</dialog>

<!-- MODAL: Status Update -->
<dialog id="statusModal">
    <div class="modal-header">
        <strong>Update Booking Status</strong>
        <button class="btn" onclick="closeStatusModal()">✕</button>
    </div>
    <div class="modal-body">
        <form id="statusForm">
            <input type="hidden" id="statusBookingId" name="booking_id">
            
            <div class="field">
                <label for="updateBookingStatus">Booking Status</label>
                <select id="updateBookingStatus" name="booking_status">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            
            <div class="field">
                <label for="updatePaymentStatus">Payment Status</label>
                <select id="updatePaymentStatus" name="payment_status">
                    <option value="pending">Pending</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="field">
                <label for="statusNotes">Notes (Optional)</label>
                <textarea id="statusNotes" name="notes" rows="3" placeholder="Add any notes about this status change..."></textarea>
            </div>
            
            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
                <button class="btn" type="button" onclick="closeStatusModal()">Cancel</button>
                <button class="btn primary" type="submit">Update Status</button>
            </div>
        </form>
    </div>
</dialog>

<script>
// Booking management functions
function showBookingEditor() {
    document.getElementById('bookingEditorTitle').textContent = 'Add New Booking';
    document.getElementById('bookingEditorForm').reset();
    document.getElementById('bookingId').value = '';
    document.getElementById('bookingReference').value = 'EVT-' + Date.now();
    document.getElementById('bookingEditorModal').showModal();
}

function editBooking(booking) {
    document.getElementById('bookingEditorTitle').textContent = 'Edit Booking';
    document.getElementById('bookingId').value = booking.booking_id;
    document.getElementById('bookingReference').value = booking.booking_reference;
    document.getElementById('bookingType').value = booking.booking_type;
    document.getElementById('eventType').value = booking.event_type;
    document.getElementById('eventDate').value = booking.event_date;
    document.getElementById('eventTime').value = booking.event_time;
    document.getElementById('guestCount').value = booking.guest_count || 50;
    document.getElementById('venueType').value = booking.venue_type;
    document.getElementById('venueSelect').value = booking.venue_id || '';
    document.getElementById('packageSelect').value = booking.package_id || '';
    document.getElementById('eventLocation').value = booking.event_location || '';
    document.getElementById('specialInstructions').value = booking.special_instructions || '';
    document.getElementById('contactName').value = booking.contact_name;
    document.getElementById('contactEmail').value = booking.contact_email;
    document.getElementById('contactPhone').value = booking.contact_phone;
    document.getElementById('alternatePhone').value = booking.alternate_phone || '';
    document.getElementById('companyName').value = booking.company_name || '';
    document.getElementById('preferredContact').value = booking.preferred_contact || 'Any';
    document.getElementById('totalAmount').value = booking.total_amount || 0;
    document.getElementById('paymentMethod').value = booking.payment_method;
    document.getElementById('paymentStatus').value = booking.payment_status;
    document.getElementById('bookingStatus').value = booking.booking_status;
    document.getElementById('bookingEditorModal').showModal();
}

function closeBookingEditor() {
    document.getElementById('bookingEditorModal').close();
}

function showStatusModal(bookingId, bookingStatus, paymentStatus) {
    document.getElementById('statusBookingId').value = bookingId;
    document.getElementById('updateBookingStatus').value = bookingStatus;
    document.getElementById('updatePaymentStatus').value = paymentStatus;
    document.getElementById('statusNotes').value = '';
    document.getElementById('statusModal').showModal();
}

function closeStatusModal() {
    document.getElementById('statusModal').close();
}

function viewBookingDetails(bookingId) {
    window.location.href = 'booking-details.php?id=' + bookingId;
}

function resetFilters() {
    window.location.href = window.location.pathname;
}

// Add this debug function
function debugFetch(url, options) {
    console.log('Making request to:', url);
    console.log('Options:', options);
    return fetch(url, options)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            return response.text().then(text => {
                console.log('Response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    return {success: false, message: 'Invalid JSON response'};
                }
            });
        })
        .catch(error => {
            console.error('Fetch error:', error);
            return {success: false, message: 'Network error: ' + error.message};
        });
}

// Form submission handlers - CORRECTED PATHS
document.getElementById('bookingEditorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const isNew = !formData.get('booking_id');
    
    // Convert empty strings to null for foreign keys
    const venueId = formData.get('venue_id');
    const packageId = formData.get('package_id');
    
    if (venueId === '') formData.set('venue_id', '');
    if (packageId === '') formData.set('package_id', '');
    
    // Use debugFetch to see what's happening
    debugFetch('functions/save-booking.php', {
        method: 'POST',
        body: formData
    })
    .then(data => {
        console.log('Received data:', data);
        if (data.success) {
            showToast(`Booking ${isNew ? 'created' : 'updated'} successfully`, 'success');
            closeBookingEditor();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Error saving booking', 'error');
        }
    });
});

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // CORRECTED PATH
    fetch('functions/update-booking-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Booking status updated successfully', 'success');
            closeStatusModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Error updating status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating status', 'error');
    });
});

function sendReminder(bookingId) {
    if (confirm('Send reminder email to customer?')) {
        // CORRECTED PATH
        fetch('functions/send-reminder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: bookingId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Reminder sent successfully', 'success');
            } else {
                showToast('Error sending reminder', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error sending reminder', 'error');
        });
    }
}

function exportBookings() {
    const params = new URLSearchParams(window.location.search);
    // CORRECTED PATH
    window.location.href = 'functions/export-bookings.php?' + params.toString();
}

// Toast function
function showToast(message, type = 'info') {
    // Create toast if it doesn't exist
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 16px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            display: none;
            font-weight: 500;
        `;
        document.body.appendChild(toast);
    }
    
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.background = type === 'error' ? '#dc3545' : 
                            type === 'success' ? '#28a745' : '#17a2b8';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}
</script>


<style>
/* Reuse the same styles from users.php */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.header-actions {
    display: flex;
    gap: 8px;
}

.filter-bar {
    display: flex;
    gap: 16px;
    align-items: center;
}

.filter-form {
    display: flex;
    gap: 16px;
    align-items: center;
    width: 100%;
}

.search-field {
    display: flex;
    flex: 1;
    max-width: 300px;
}

.search-field input {
    flex: 1;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.search-field button {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.filter-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.data-table th {
    font-weight: 600;
    background: #f8f9fa;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-warning {
    background: #ffc107;
    color: white;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 4px;
}

.btn-icon {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    border-radius: 4px;
}

.btn-icon:hover {
    background: #e9ecef;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.muted {
    color: #6c757d;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.card {
    background: white;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card .big {
    font-size: 2rem;
    font-weight: bold;
    margin: 8px 0;
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-field {
        max-width: none;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .data-table {
        font-size: 0.875rem;
    }
    
    .cards {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .cards {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
}
</style>

<?php include __DIR__ . '/admin-components/admin-footer.php';?>