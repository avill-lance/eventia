<?php 
include __DIR__ . '/admin-components/admin-header.php';
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

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

!-- STATUS UPDATE MODAL -->
<dialog id="statusModal">
    <div class="modal-header">
        <strong style="font-size: 18px;">Update Booking Status</strong>
        <button class="btn" onclick="closeStatusModal()" style="padding: 6px 10px;">✕</button>
    </div>
    <div class="modal-body">
        <form id="statusForm" novalidate>
            <input type="hidden" id="statusBookingId" name="booking_id">
            
            <div class="form">
                <div class="field">
                    <label for="updateBookingStatus">Booking Status</label>
                    <select id="updateBookingStatus" name="booking_status" style="width: 100%;">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="field">
                    <label for="updatePaymentStatus">Payment Status</label>
                    <select id="updatePaymentStatus" name="payment_status" style="width: 100%;">
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="statusNotes">Notes (Optional)</label>
                    <textarea id="statusNotes" name="notes" rows="3" style="width: 100%; resize: vertical;" placeholder="Add any notes about this status change..."></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="button" class="btn" onclick="closeStatusModal()">Cancel</button>
                <button type="submit" class="btn primary" style="display: flex; align-items: center; gap: 6px;">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- BOOKINGS MANAGEMENT -->
<section id="view-bookings" class="view">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; font-size: 24px; font-weight: 700;">Bookings Management</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn" onclick="exportBookings()" style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-download"></i> Export
            </button>
            <button class="btn primary" onclick="showBookingEditor()" style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-plus-circle"></i> Add New Booking
            </button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert-toast success" style="background: var(--ok); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; border: 1px solid rgba(34, 197, 94, 0.25);">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; margin-left: 12px; cursor: pointer;">✕</button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert-toast danger" style="background: var(--danger); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; border: 1px solid rgba(239, 68, 68, 0.25);">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; margin-left: 12px; cursor: pointer;">✕</button>
        </div>
    <?php endif; ?>

    <!-- Booking Statistics Cards -->
    <div class="cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="background: var(--panel); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Total Bookings</h3>
            <div style="font-size: 28px; font-weight: 700; color: var(--text);"><?php echo htmlspecialchars($bookingStats['totalBookings'] ?? 0); ?></div>
            <div style="color: var(--muted); font-size: 12px; margin-top: 4px;">All bookings</div>
        </div>
        <div class="card" style="background: var(--panel); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Confirmed</h3>
            <div style="font-size: 28px; font-weight: 700; color: var(--ok);"><?php echo htmlspecialchars($bookingStats['confirmedBookings'] ?? 0); ?></div>
            <div style="color: var(--muted); font-size: 12px; margin-top: 4px;">Active events</div>
        </div>
        <div class="card" style="background: var(--panel); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Pending</h3>
            <div style="font-size: 28px; font-weight: 700; color: var(--warn);"><?php echo htmlspecialchars($bookingStats['pendingBookings'] ?? 0); ?></div>
            <div style="color: var(--muted); font-size: 12px; margin-top: 4px;">Awaiting confirmation</div>
        </div>
        <div class="card" style="background: var(--panel); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Revenue</h3>
            <div style="font-size: 28px; font-weight: 700; color: var(--ok);">₱<?php echo number_format($bookingStats['totalRevenue'] ?? 0, 2); ?></div>
            <div style="color: var(--muted); font-size: 12px; margin-top: 4px;">Total paid</div>
        </div>
        <div class="card" style="background: var(--panel); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Upcoming</h3>
            <div style="font-size: 28px; font-weight: 700; color: var(--brand);"><?php echo htmlspecialchars($bookingStats['upcomingEvents'] ?? 0); ?></div>
            <div style="color: var(--muted); font-size: 12px; margin-top: 4px;">Future events</div>
        </div>
    </div>

    <!-- Advanced Filter Panel -->
    <div class="card filter-panel" style="margin-bottom: 24px; border-left: 4px solid var(--brand);">
        <div class="card-body">
            <h4 style="margin: 0 0 16px 0; color: var(--text); font-size: 16px;">Filter Bookings</h4>
            <form method="GET" class="form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div class="field">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search bookings..." value="<?php echo htmlspecialchars($searchTerm); ?>" style="width: 100%;">
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status" style="width: 100%;" onchange="this.form.submit()">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="field">
                    <label>Type</label>
                    <select name="type" style="width: 100%;" onchange="this.form.submit()">
                        <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="self" <?php echo $typeFilter === 'self' ? 'selected' : ''; ?>>Self Service</option>
                        <option value="guided" <?php echo $typeFilter === 'guided' ? 'selected' : ''; ?>>Guided</option>
                    </select>
                </div>
                <div class="field">
                    <label>Event Date</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>" style="width: 100%;" onchange="this.form.submit()">
                </div>
                <div class="field" style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn" onclick="resetFilters()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions-panel" id="bulkActionsPanel" style="display: none; margin-bottom: 16px; background: var(--warn); border-left: 4px solid var(--warn); border-radius: 12px; padding: 12px 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span id="selectedCount" style="font-weight: 600; color: white;">0 bookings selected</span>
            <div style="display: flex; gap: 8px;">
                <button class="btn ok" onclick="bulkUpdateStatus('confirmed')" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-check-circle"></i> Confirm
                </button>
                <button class="btn warn" onclick="bulkUpdateStatus('pending')" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-pause-circle"></i> Set Pending
                </button>
                <button class="btn danger" onclick="bulkDelete()" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table id="bookingsTable" class="table" style="width: 100%; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th width="40" style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" style="cursor: pointer;">
                            </th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Booking Details</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Customer</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Package & Venue</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Financials</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Status</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Event Date</th>
                            <th width="180" style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $booking): ?>
                            <tr data-booking-id="<?php echo $booking['booking_id']; ?>" style="transition: all 0.2s ease;">
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <input type="checkbox" class="booking-checkbox" value="<?php echo $booking['booking_id']; ?>" onchange="updateBulkActions()" style="cursor: pointer;">
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="font-weight: 600; color: var(--text); margin-bottom: 4px;">
                                        <?php echo htmlspecialchars($booking['booking_reference']); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                        <?php echo htmlspecialchars($booking['event_type']); ?> • 
                                        <?php echo htmlspecialchars($booking['event_time']); ?>
                                    </div>
                                    <div class="status" style="background: rgba(79, 70, 229, 0.12); border-color: rgba(79, 70, 229, 0.25); margin-top: 4px;">
                                        <?php echo ucfirst($booking['booking_type']); ?>
                                    </div>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <?php if ($booking['first_name']): ?>
                                        <div style="font-weight: 600; color: var(--text); margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                            <?php echo htmlspecialchars($booking['user_email']); ?>
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                            <?php echo htmlspecialchars($booking['user_phone'] ?? ''); ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="font-weight: 600; color: var(--text); margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($booking['contact_name']); ?>
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                            <?php echo htmlspecialchars($booking['contact_email']); ?>
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                            <?php echo htmlspecialchars($booking['contact_phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="font-weight: 600; color: var(--text); margin-bottom: 4px;">
                                        <?php echo htmlspecialchars($booking['package_name'] ?? 'Custom'); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                        <?php echo htmlspecialchars($booking['venue_name'] ?? $booking['venue_type']); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                        <?php echo htmlspecialchars($booking['service_count']); ?> services • 
                                        <?php echo htmlspecialchars($booking['guest_count'] ?? 0); ?> guests
                                    </div>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="font-weight: 700; color: var(--ok); margin-bottom: 4px;">
                                        ₱<?php echo number_format($booking['total_amount'], 2); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                        <?php echo ucfirst($booking['payment_method']); ?>
                                    </div>
                                    <div class="status <?php 
                                        echo $booking['payment_status'] === 'paid' ? 'live' : 
                                             ($booking['payment_status'] === 'partial' ? 'draft' : 'draft'); 
                                    ?>" style="margin-top: 4px;">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </div>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <span class="status <?php 
                                        echo $booking['booking_status'] === 'confirmed' ? 'live' : 
                                             ($booking['booking_status'] === 'pending' ? 'draft' : 
                                             ($booking['booking_status'] === 'cancelled' ? 'draft' : 'draft')); 
                                    ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                    <?php 
                                    $eventDate = strtotime($booking['event_date']);
                                    $today = strtotime('today');
                                    $daysDiff = ($eventDate - $today) / (60 * 60 * 24);
                                    
                                    if ($daysDiff < 0 && $booking['booking_status'] !== 'completed' && $booking['booking_status'] !== 'cancelled'): ?>
                                        <div class="status" style="background: rgba(107, 114, 128, 0.12); border-color: rgba(107, 114, 128, 0.25); margin-top: 4px;">
                                            Past Due
                                        </div>
                                    <?php elseif ($daysDiff >= 0 && $daysDiff <= 7): ?>
                                        <div class="status" style="background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.25); margin-top: 4px;">
                                            In <?php echo $daysDiff; ?> days
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="color: var(--text); font-weight: 500; margin-bottom: 4px;">
                                        <?php echo date('M j, Y', strtotime($booking['event_date'])); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 12px;">
                                        <?php echo date('M j, Y', strtotime($booking['created_at'])); ?>
                                    </div>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <button class="btn" onclick="viewBookingDetails(<?php echo $booking['booking_id']; ?>)" title="View Details" style="padding: 6px 10px; font-size: 12px;">
                                            <i class="bi bi-eye">View</i>
                                        </button>
                                        <button class="btn primary" onclick="editBooking(<?php echo htmlspecialchars(json_encode($booking)); ?>)" title="Edit" style="padding: 6px 10px; font-size: 12px;">
                                            <i class="bi bi-pencil">Edit</i>
                                        </button>
                                        <button class="btn warn" onclick="showStatusModal(<?php echo $booking['booking_id']; ?>, '<?php echo $booking['booking_status']; ?>', '<?php echo $booking['payment_status']; ?>')" title="Update Status" style="padding: 6px 10px; font-size: 12px;">
                                            <i class="bi bi-arrow-repeat">Update</i>
                                        </button>
                                        <button class="btn danger" onclick="deleteBooking(<?php echo $booking['booking_id']; ?>, '<?php echo htmlspecialchars(addslashes($booking['booking_reference'])); ?>')" title="Delete" style="padding: 6px 10px; font-size: 12px;">
                                            <i class="bi bi-trash">Delete</i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--muted);">
                                    No bookings found matching your criteria
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- BOOKING EDITOR MODAL -->
<dialog id="bookingEditorModal">
    <div class="modal-header">
        <strong id="bookingEditorTitle" style="font-size: 18px;">Add New Booking</strong>
        <button class="btn" onclick="closeBookingEditor()" style="padding: 6px 10px;">✕</button>
    </div>
    <div class="modal-body">
        <form id="bookingEditorForm" novalidate>
            <input type="hidden" id="bookingId" name="booking_id">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="save">
            
            <div class="form">
                <div class="field">
                    <label for="bookingReference">Booking Reference <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="bookingReference" name="booking_reference" required style="width: 100%;" 
                           value="EVT-<?php echo time(); ?>" readonly>
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Booking reference is required.</div>
                </div>
                <div class="field">
                    <label for="bookingType">Booking Type <span style="color: var(--danger);">*</span></label>
                    <select id="bookingType" name="booking_type" required style="width: 100%;">
                        <option value="self">Self Service</option>
                        <option value="guided">Guided</option>
                    </select>
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please select a booking type.</div>
                </div>
                <div class="field">
                    <label for="eventType">Event Type <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="eventType" name="event_type" required style="width: 100%;" 
                           placeholder="e.g., Wedding, Birthday, Corporate">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Event type is required.</div>
                </div>
                <div class="field">
                    <label for="eventDate">Event Date <span style="color: var(--danger);">*</span></label>
                    <input type="date" id="eventDate" name="event_date" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Event date is required.</div>
                </div>
                <div class="field">
                    <label for="eventTime">Event Time</label>
                    <input type="text" id="eventTime" name="event_time" style="width: 100%;" placeholder="e.g., 2:00 PM">
                </div>
                <div class="field">
                    <label for="guestCount">Guest Count</label>
                    <input type="number" id="guestCount" name="guest_count" min="1" value="50" style="width: 100%;">
                </div>
                <div class="field">
                    <label for="venueType">Venue Type</label>
                    <select id="venueType" name="venue_type" style="width: 100%;">
                        <option value="own">Own Venue</option>
                        <option value="rental">Rental</option>
                    </select>
                </div>
                <div class="field">
                    <label for="venueSelect">Venue</label>
                    <select id="venueSelect" name="venue_id" style="width: 100%;">
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
                    <select id="packageSelect" name="package_id" style="width: 100%;">
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
                    <input type="text" id="eventLocation" name="event_location" style="width: 100%;" 
                           placeholder="Full event address">
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="specialInstructions">Special Instructions</label>
                    <textarea id="specialInstructions" name="special_instructions" rows="3" style="width: 100%; resize: vertical;" 
                              placeholder="Any special requirements or notes"></textarea>
                </div>
            </div>
            
            <h3 style="margin: 20px 0 10px 0; color: var(--text); font-size: 16px;">Customer Information</h3>
            <div class="form">
                <div class="field">
                    <label for="contactName">Contact Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="contactName" name="contact_name" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Contact name is required.</div>
                </div>
                <div class="field">
                    <label for="contactEmail">Contact Email <span style="color: var(--danger);">*</span></label>
                    <input type="email" id="contactEmail" name="contact_email" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Valid email is required.</div>
                </div>
                <div class="field">
                    <label for="contactPhone">Contact Phone <span style="color: var(--danger);">*</span></label>
                    <input type="tel" id="contactPhone" name="contact_phone" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Contact phone is required.</div>
                </div>
                <div class="field">
                    <label for="alternatePhone">Alternate Phone</label>
                    <input type="tel" id="alternatePhone" name="alternate_phone" style="width: 100%;">
                </div>
                <div class="field">
                    <label for="companyName">Company Name</label>
                    <input type="text" id="companyName" name="company_name" style="width: 100%;">
                </div>
                <div class="field">
                    <label for="preferredContact">Preferred Contact</label>
                    <select id="preferredContact" name="preferred_contact" style="width: 100%;">
                        <option value="Any">Any</option>
                        <option value="Email">Email</option>
                        <option value="Phone">Phone</option>
                        <option value="SMS">SMS</option>
                    </select>
                </div>
            </div>
            
            <h3 style="margin: 20px 0 10px 0; color: var(--text); font-size: 16px;">Payment Information</h3>
            <div class="form">
                <div class="field">
                    <label for="totalAmount">Total Amount (₱)</label>
                    <input type="number" id="totalAmount" name="total_amount" step="0.01" min="0" value="0" style="width: 100%;">
                </div>
                <div class="field">
                    <label for="paymentMethod">Payment Method</label>
                    <select id="paymentMethod" name="payment_method" style="width: 100%;">
                        <option value="GCash">GCash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Installment">Installment</option>
                    </select>
                </div>
                <div class="field">
                    <label for="paymentStatus">Payment Status</label>
                    <select id="paymentStatus" name="payment_status" style="width: 100%;">
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="field">
                    <label for="bookingStatus">Booking Status</label>
                    <select id="bookingStatus" name="booking_status" style="width: 100%;">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="button" class="btn" onclick="closeBookingEditor()">Cancel</button>
                <button type="submit" class="btn primary" id="bookingSaveBtn" style="display: flex; align-items: center; gap: 6px;">
                    <span id="saveSpinner" style="display: none;">⏳</span>
                    Save Booking
                </button>
            </div>
        </form>
    </div>
</dialog>
<script>
// Booking Modal Functions
function showBookingEditor() {
    const modal = document.getElementById('bookingEditorModal');
    const title = document.getElementById('bookingEditorTitle');
    const form = document.getElementById('bookingEditorForm');
    
    title.textContent = 'Add New Booking';
    form.reset();
    form.classList.remove('was-validated');
    document.getElementById('bookingId').value = '';
    document.getElementById('bookingReference').value = 'EVT-' + Date.now();
    document.getElementById('bookingStatus').value = 'pending';
    document.getElementById('paymentStatus').value = 'pending';
    
    modal.showModal();
}

function closeBookingEditor() {
    document.getElementById('bookingEditorModal').close();
}

function closeStatusModal() {
    document.getElementById('statusModal').close();
}

// Edit booking function
function editBooking(booking) {
    const modal = document.getElementById('bookingEditorModal');
    const title = document.getElementById('bookingEditorTitle');
    
    title.textContent = 'Edit Booking';
    document.getElementById('bookingId').value = booking.booking_id;
    document.getElementById('bookingReference').value = booking.booking_reference;
    document.getElementById('bookingType').value = booking.booking_type;
    document.getElementById('eventType').value = booking.event_type;
    document.getElementById('eventDate').value = booking.event_date;
    document.getElementById('eventTime').value = booking.event_time || '';
    document.getElementById('guestCount').value = booking.guest_count || 50;
    document.getElementById('venueType').value = booking.venue_type || 'own';
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
    
    modal.showModal();
}

// Show status modal
function showStatusModal(bookingId, bookingStatus, paymentStatus) {
    document.getElementById('statusBookingId').value = bookingId;
    document.getElementById('updateBookingStatus').value = bookingStatus;
    document.getElementById('updatePaymentStatus').value = paymentStatus;
    document.getElementById('statusNotes').value = '';
    document.getElementById('statusModal').showModal();
}

// View booking details
async function viewBookingDetails(bookingId) {
    showLoading();
    try {
        const response = await fetch(`bookings-ajax.php?action=get_booking_details&booking_id=${bookingId}`);
        const bookingData = await response.json();
        
        if (bookingData.success) {
            // Create a details modal similar to packages.php
            showBookingDetailsModal(bookingData.data);
        } else {
            showToast('Error loading booking details: ' + (bookingData.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Show booking details modal
function showBookingDetailsModal(booking) {
    // Create modal HTML if it doesn't exist
    let modal = document.getElementById('bookingDetailsModal');
    if (!modal) {
        modal = document.createElement('dialog');
        modal.id = 'bookingDetailsModal';
        modal.innerHTML = `
            <div class="modal-header">
                <strong style="font-size: 18px;">Booking Details</strong>
                <button class="btn" onclick="closeBookingDetailsModal()" style="padding: 6px 10px;">✕</button>
            </div>
            <div class="modal-body" id="bookingDetailsContent" style="max-height: 60vh; overflow-y: auto; padding: 20px;">
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Populate with booking details
    const content = document.getElementById('bookingDetailsContent');
    content.innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4 style="margin: 0 0 12px 0; color: var(--text);">Booking Information</h4>
                <div style="display: grid; gap: 8px;">
                    <div><strong>Reference:</strong> ${booking.booking_reference}</div>
                    <div><strong>Type:</strong> ${booking.booking_type}</div>
                    <div><strong>Event:</strong> ${booking.event_type}</div>
                    <div><strong>Date:</strong> ${new Date(booking.event_date).toLocaleDateString()}</div>
                    <div><strong>Time:</strong> ${booking.event_time || 'Not specified'}</div>
                    <div><strong>Guests:</strong> ${booking.guest_count}</div>
                </div>
            </div>
            <div>
                <h4 style="margin: 0 0 12px 0; color: var(--text);">Customer Information</h4>
                <div style="display: grid; gap: 8px;">
                    <div><strong>Name:</strong> ${booking.contact_name}</div>
                    <div><strong>Email:</strong> ${booking.contact_email}</div>
                    <div><strong>Phone:</strong> ${booking.contact_phone}</div>
                    <div><strong>Company:</strong> ${booking.company_name || 'N/A'}</div>
                </div>
            </div>
        </div>
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
            <h4 style="margin: 0 0 12px 0; color: var(--text);">Financial Information</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div><strong>Total Amount:</strong> ₱${parseFloat(booking.total_amount).toFixed(2)}</div>
                    <div><strong>Payment Method:</strong> ${booking.payment_method}</div>
                </div>
                <div>
                    <div><strong>Payment Status:</strong> <span class="status ${booking.payment_status === 'paid' ? 'live' : 'draft'}">${booking.payment_status}</span></div>
                    <div><strong>Booking Status:</strong> <span class="status ${booking.booking_status === 'confirmed' ? 'live' : 'draft'}">${booking.booking_status}</span></div>
                </div>
            </div>
        </div>
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
            <h4 style="margin: 0 0 12px 0; color: var(--text);">Additional Information</h4>
            <div><strong>Venue:</strong> ${booking.venue_name || booking.venue_type}</div>
            <div><strong>Package:</strong> ${booking.package_name || 'Custom'}</div>
            <div><strong>Location:</strong> ${booking.event_location || 'Not specified'}</div>
            <div style="margin-top: 8px;"><strong>Special Instructions:</strong></div>
            <div style="background: var(--panel-2); padding: 12px; border-radius: 8px; margin-top: 4px;">
                ${booking.special_instructions || 'No special instructions provided.'}
            </div>
        </div>
    `;
    
    modal.showModal();
}

function closeBookingDetailsModal() {
    document.getElementById('bookingDetailsModal').close();
}

// Bulk Actions Functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.booking-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const selected = document.querySelectorAll('.booking-checkbox:checked');
    const bulkPanel = document.getElementById('bulkActionsPanel');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = `${selected.length} booking(s) selected`;
    bulkPanel.style.display = selected.length > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const totalCheckboxes = document.querySelectorAll('.booking-checkbox').length;
    const selectAll = document.getElementById('selectAll');
    selectAll.checked = selected.length === totalCheckboxes && totalCheckboxes > 0;
    selectAll.indeterminate = selected.length > 0 && selected.length < totalCheckboxes;
}

async function bulkUpdateStatus(status) {
    const selected = Array.from(document.querySelectorAll('.booking-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        showToast('Please select at least one booking', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to set ${selected.length} booking(s) to "${status}"?`)) {
        return;
    }
    
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'bulk_update_status');
        formData.append('booking_ids', JSON.stringify(selected));
        formData.append('status', status);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('bookings-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(`Successfully updated ${selected.length} booking(s)`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error updating bookings: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

function bulkDelete() {
    const selected = Array.from(document.querySelectorAll('.booking-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        showToast('Please select at least one booking', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selected.length} booking(s)? This action cannot be undone.`)) {
        performBulkDelete(selected);
    }
}

async function performBulkDelete(bookingIds) {
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'bulk_delete');
        formData.append('booking_ids', JSON.stringify(bookingIds));
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('bookings-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(`Successfully deleted ${bookingIds.length} booking(s)`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error deleting bookings: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Delete single booking
function deleteBooking(bookingId, bookingReference) {
    if (confirm(`Are you sure you want to delete booking "${bookingReference}"? This action cannot be undone.`)) {
        performSingleDelete(bookingId);
    }
}

async function performSingleDelete(bookingId) {
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('booking_id', bookingId);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('bookings-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Booking deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error deleting booking: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Form validation and submission
document.getElementById('bookingEditorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Basic validation
    const reference = document.getElementById('bookingReference');
    const bookingType = document.getElementById('bookingType');
    const eventType = document.getElementById('eventType');
    const eventDate = document.getElementById('eventDate');
    const contactName = document.getElementById('contactName');
    const contactEmail = document.getElementById('contactEmail');
    const contactPhone = document.getElementById('contactPhone');
    
    let isValid = true;
    
    if (!reference.value.trim()) {
        showFieldError(reference, 'Booking reference is required');
        isValid = false;
    } else {
        clearFieldError(reference);
    }
    
    if (!bookingType.value) {
        showFieldError(bookingType, 'Booking type is required');
        isValid = false;
    } else {
        clearFieldError(bookingType);
    }
    
    if (!eventType.value.trim()) {
        showFieldError(eventType, 'Event type is required');
        isValid = false;
    } else {
        clearFieldError(eventType);
    }
    
    if (!eventDate.value) {
        showFieldError(eventDate, 'Event date is required');
        isValid = false;
    } else {
        clearFieldError(eventDate);
    }
    
    if (!contactName.value.trim()) {
        showFieldError(contactName, 'Contact name is required');
        isValid = false;
    } else {
        clearFieldError(contactName);
    }
    
    if (!contactEmail.value.trim() || !isValidEmail(contactEmail.value)) {
        showFieldError(contactEmail, 'Valid email is required');
        isValid = false;
    } else {
        clearFieldError(contactEmail);
    }
    
    if (!contactPhone.value.trim()) {
        showFieldError(contactPhone, 'Contact phone is required');
        isValid = false;
    } else {
        clearFieldError(contactPhone);
    }
    
    if (!isValid) {
        showToast('Please fill all required fields correctly', 'error');
        return;
    }
    
    const saveBtn = document.getElementById('bookingSaveBtn');
    const spinner = document.getElementById('saveSpinner');
    const formData = new FormData(this);
    
    saveBtn.disabled = true;
    spinner.style.display = 'inline';
    
    try {
        const response = await fetch('bookings-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            closeBookingEditor();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        saveBtn.disabled = false;
        spinner.style.display = 'none';
    }
});

// Status form submission
document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
    formData.append('action', 'update_status');
    
    try {
        const response = await fetch('bookings-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Booking status updated successfully', 'success');
            closeStatusModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error updating status: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});

// Utility functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    field.style.borderColor = 'var(--danger)';
    const feedback = field.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
        feedback.style.display = 'block';
    }
}

function clearFieldError(field) {
    field.style.borderColor = 'var(--border)';
    const feedback = field.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.style.display = 'none';
    }
}

function resetFilters() {
    window.location.href = window.location.pathname;
}

function showLoading() {
    // Create loading overlay if it doesn't exist
    let spinner = document.getElementById('globalSpinner');
    if (!spinner) {
        spinner = document.createElement('div');
        spinner.id = 'globalSpinner';
        spinner.style.cssText = `
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(15, 23, 42, 0.8); 
            z-index: 9999; 
            justify-content: center; 
            align-items: center;
        `;
        spinner.innerHTML = `
            <div style="background: var(--panel); padding: 24px; border-radius: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 12px;">
                <div style="width: 24px; height: 24px; border: 2px solid var(--border); border-top: 2px solid var(--brand); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <span>Loading...</span>
            </div>
        `;
        document.body.appendChild(spinner);
    }
    spinner.style.display = 'flex';
}

function hideLoading() {
    const spinner = document.getElementById('globalSpinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}

function showToast(message, type = 'info') {
    // Remove any existing toasts
    const existingToasts = document.querySelectorAll('.alert-toast:not(.success):not(.danger)');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `alert-toast ${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        padding: 12px 16px;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        border: 1px solid;
        animation: slideIn 0.3s ease;
    `;
    
    const bgColor = type === 'error' ? 'var(--danger)' : 
                   type === 'success' ? 'var(--ok)' : 
                   type === 'warning' ? 'var(--warn)' : 'var(--brand)';
    
    toast.style.background = bgColor;
    toast.style.borderColor = type === 'error' ? 'rgba(239, 68, 68, 0.25)' : 
                             type === 'success' ? 'rgba(34, 197, 94, 0.25)' : 
                             type === 'warning' ? 'rgba(245, 158, 11, 0.25)' : 'rgba(79, 70, 229, 0.25)';
    
    toast.innerHTML = `
        ${message}
        <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; margin-left: 12px; cursor: pointer; float: right;">✕</button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Export function
function exportBookings() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = 'functions/export-bookings.php?' + params.toString();
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    #bookingsTable tbody tr:hover {
        background: rgba(79, 70, 229, 0.05);
        transform: translateY(-1px);
    }
    
    .status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        border: 1px solid var(--border);
        font-size: 12px;
        font-weight: 500;
    }
    
    .status.live {
        background: rgba(34, 197, 94, 0.12);
        border-color: rgba(34, 197, 94, 0.25);
        color: var(--ok);
    }
    
    .status.draft {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.25);
        color: var(--warn);
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .section-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start !important;
        }
        
        .form {
            grid-template-columns: 1fr !important;
        }
        
        #bookingsTable {
            font-size: 14px;
        }
        
        #bookingsTable td {
            padding: 12px 8px !important;
        }
        
        .bulk-actions-panel > div {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start !important;
        }
    }
    
    /* Custom scrollbar for modal */
    #bookingDetailsContent::-webkit-scrollbar {
        width: 6px;
    }
    
    #bookingDetailsContent::-webkit-scrollbar-track {
        background: var(--panel-2);
        border-radius: 3px;
    }
    
    #bookingDetailsContent::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 3px;
    }
    
    #bookingDetailsContent::-webkit-scrollbar-thumb:hover {
        background: var(--muted);
    }
`;
document.head.appendChild(style);

// Initialize table filtering
document.addEventListener('DOMContentLoaded', function() {
    // Simple table filtering based on URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search') || '';
    const statusFilter = urlParams.get('status') || 'all';
    const typeFilter = urlParams.get('type') || 'all';
    const dateFilter = urlParams.get('date') || '';
    
    if (searchTerm || statusFilter !== 'all' || typeFilter !== 'all' || dateFilter) {
        filterTable();
    }
});

function filterTable() {
    const searchTerm = document.querySelector('input[name="search"]').value.toLowerCase();
    const statusFilter = document.querySelector('select[name="status"]').value;
    const typeFilter = document.querySelector('select[name="type"]').value;
    const dateFilter = document.querySelector('input[name="date"]').value;
    
    const rows = document.querySelectorAll('#bookingsTable tbody tr');
    
    rows.forEach(row => {
        const reference = row.cells[1].textContent.toLowerCase();
        const customer = row.cells[2].textContent.toLowerCase();
        const status = row.cells[5].querySelector('.status').textContent.toLowerCase();
        const type = row.cells[1].querySelector('.status').textContent.toLowerCase();
        const eventDate = row.cells[6].textContent.trim();
        
        const searchMatch = !searchTerm || reference.includes(searchTerm) || customer.includes(searchTerm);
        const statusMatch = statusFilter === 'all' || status === statusFilter.toLowerCase();
        const typeMatch = typeFilter === 'all' || type === typeFilter.toLowerCase();
        const dateMatch = !dateFilter || eventDate.includes(dateFilter);
        
        row.style.display = searchMatch && statusMatch && typeMatch && dateMatch ? '' : 'none';
    });
}
</script>
                   