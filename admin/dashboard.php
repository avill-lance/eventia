<?php 
include __DIR__ . '/admin-components/admin-header.php';
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Fetch dashboard data
$stats = [];
$recentPayments = [];
$recentBookings = [];

try {
    // Fetch statistics using direct queries
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_users WHERE status = 'active'");
    $stats['totalUsers'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings");
    $stats['totalBookings'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_packages WHERE status = 'active'");
    $stats['totalPackages'] = $result->fetch_assoc()['total'];
    
    // Calculate total revenue from both sources
    $booking_revenue = 0;
    $result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tbl_bookings WHERE payment_status = 'paid'");
    if ($result) {
        $booking_revenue = $result->fetch_assoc()['total'];
    }
    
    $transaction_revenue = 0;
    $result = $conn->query("SELECT COALESCE(SUM(price), 0) as total FROM tbl_transactions WHERE status = 'PAID'");
    if ($result) {
        $transaction_revenue = $result->fetch_assoc()['total'];
    }
    
    $stats['totalRevenue'] = $booking_revenue + $transaction_revenue;
    
    // Fetch additional statistics using functions
    $stats['totalServices'] = getServicesCount($conn);
    $stats['totalVenues'] = getActiveVenues($conn);
    $stats['pendingInquiries'] = getPendingInquiries($conn);
    
    // NEW: Enhanced statistics
    $stats['pendingBookings'] = getPendingBookingsCount($conn);
    $stats['todaysRevenue'] = getTodaysRevenue($conn);
    $stats['paidBookings'] = getPaidBookingsCount($conn);
    
    $recentBookings = getRecentBookings($conn, 5);
    
    // Fetch recent payments
    $stmt = $conn->prepare("
        SELECT t.transaction_id, t.ref_id, t.price, t.date_time, t.status,
               u.first_name, u.last_name, u.email
        FROM tbl_transactions t
        LEFT JOIN tbl_users u ON t.user_id = u.user_id
        WHERE t.status = 'PAID'
        ORDER BY t.date_time DESC
        LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $recentPayments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
} catch (Exception $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
}

$conn->close();
?>

<!-- DASHBOARD -->
<section id="view-dashboard" class="view">
    <div class="cards" id="statsCards">
        <div class="card">
            <h3>Total Users</h3>
            <div class="big" id="totalUsers"><?php echo htmlspecialchars($stats['totalUsers'] ?? 0); ?></div>
            <div class="muted">Registered users</div>
        </div>
        <div class="card">
            <h3>Total Bookings</h3>
            <div class="big" id="totalBookings"><?php echo htmlspecialchars($stats['totalBookings'] ?? 0); ?></div>
            <div class="muted">All bookings</div>
        </div>
        <div class="card">
            <h3>Active Packages</h3>
            <div class="big" id="totalPackages"><?php echo htmlspecialchars($stats['totalPackages'] ?? 0); ?></div>
            <div class="muted">Available packages</div>
        </div>
        <div class="card">
            <h3>Total Revenue</h3>
            <div class="big" id="totalRevenue">₱<?php echo number_format($stats['totalRevenue'] ?? 0, 2); ?></div>
            <div class="muted">All-time income</div>
        </div>
    </div>
    
    <!-- NEW: Enhanced Statistics Row -->
    <div class="cards" style="margin-top:16px">
        <div class="card">
            <h3>Pending Bookings</h3>
            <div class="big" id="pendingBookings"><?php echo htmlspecialchars($stats['pendingBookings'] ?? 0); ?></div>
            <div class="muted">Awaiting confirmation</div>
        </div>
        <div class="card">
            <h3>Today's Revenue</h3>
            <div class="big" id="todaysRevenue">₱<?php echo number_format($stats['todaysRevenue'] ?? 0, 2); ?></div>
            <div class="muted">Income today</div>
        </div>
        <div class="card">
            <h3>Paid Bookings</h3>
            <div class="big" id="paidBookings"><?php echo htmlspecialchars($stats['paidBookings'] ?? 0); ?></div>
            <div class="muted">Confirmed payments</div>
        </div>
        <div class="card">
            <h3>Success Rate</h3>
            <div class="big" id="successRate">
                <?php 
                $successRate = ($stats['totalUsers'] > 0 && isset($stats['paidBookings'])) 
                    ? round(($stats['paidBookings'] / $stats['totalUsers']) * 100) 
                    : 0;
                echo htmlspecialchars($successRate) . '%'; 
                ?>
            </div>
            <div class="muted">Booking conversion</div>
        </div>
    </div>
    
    <div class="grid-2" style="margin-top:16px">
        <div class="card">
            <h3>Recent Payments</h3>
            <div id="recentActivity">
                <?php if (!empty($recentPayments)): ?>
                    <div class="activity-list">
                        <?php foreach ($recentPayments as $payment): ?>
                            <div class="activity-item" style="padding: 12px; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; justify-content: between; align-items: center;">
                                    <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong>
                                    <span class="muted" style="font-size: 0.875rem;">
                                        ₱<?php echo number_format($payment['price'], 2); ?>
                                    </span>
                                </div>
                                <div class="muted" style="font-size: 0.875rem;">
                                    Ref: <?php echo htmlspecialchars($payment['ref_id']); ?> • 
                                    <?php echo date('M j, Y g:i A', strtotime($payment['date_time'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="muted" style="text-align: center; padding: 20px;">
                        No recent payments found
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <h3>Recent Bookings</h3>
            <div id="recentBookings">
                <?php if (!empty($recentBookings)): ?>
                    <div class="activity-list">
                        <?php foreach ($recentBookings as $booking): ?>
                            <div class="activity-item" style="padding: 12px; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; justify-content: between; align-items: center;">
                                    <strong><?php echo htmlspecialchars($booking['contact_name'] ?: 'Guest'); ?></strong>
                                    <span class="muted" style="font-size: 0.875rem;">
                                        ₱<?php echo number_format($booking['total_amount'] ?? 0, 2); ?>
                                    </span>
                                </div>
                                <div class="muted" style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($booking['event_type']); ?> • 
                                    <?php echo date('M j, Y', strtotime($booking['event_date'])); ?>
                                </div>
                                <div class="muted" style="font-size: 0.75rem;">
                                    Ref: <?php echo htmlspecialchars($booking['booking_reference']); ?> •
                                    Status: <span style="color: <?php echo $booking['booking_status'] == 'confirmed' ? 'green' : 'orange'; ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="muted" style="text-align: center; padding: 20px;">
                        No recent bookings found
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="cards" style="margin-top:16px">
        <div class="card">
            <h3>Active Services</h3>
            <div class="big"><?php echo htmlspecialchars($stats['totalServices'] ?? 0); ?></div>
            <div class="muted">Available services</div>
        </div>
        <div class="card">
            <h3>Available Venues</h3>
            <div class="big"><?php echo htmlspecialchars($stats['totalVenues'] ?? 0); ?></div>
            <div class="muted">Venue locations</div>
        </div>
        <div class="card">
            <h3>Pending Inquiries</h3>
            <div class="big"><?php echo htmlspecialchars($stats['pendingInquiries'] ?? 0); ?></div>
            <div class="muted">Customer inquiries</div>
        </div>
        <div class="card">
            <h3>Booking Rate</h3>
            <div class="big">
                <?php 
                $bookingRate = ($stats['totalUsers'] > 0) 
                    ? round(($stats['totalBookings'] / $stats['totalUsers']) * 100) 
                    : 0;
                echo htmlspecialchars($bookingRate) . '%'; 
                ?>
            </div>
            <div class="muted">Overall booking rate</div>
        </div>
    </div>
</section>

<!-- Other view sections remain the same -->
<section id="view-packages" class="view" hidden></section>
<section id="view-services" class="view" hidden></section>
<section id="view-blog" class="view" hidden></section>
<section id="view-bookings" class="view" hidden></section>
<section id="view-products" class="view" hidden></section>
<section id="view-inquiries" class="view" hidden></section>
<section id="view-reviews" class="view" hidden></section>

<!-- SETTINGS -->
<section id="view-settings" class="view" hidden>
    <!-- Settings content remains the same -->
</section>

</main>
</div>

<!-- MODAL: Editor -->
<dialog id="editorModal">
    <div class="modal-header">
        <strong id="editorTitle">New Item</strong>
        <button class="btn" onclick="editor.close()">✕</button>
    </div>
    <div class="modal-body">
        <form id="editorForm" enctype="multipart/form-data">
            <input type="hidden" id="editorId" name="id">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="field"><label>Name / Title</label><input id="ed-name" required name="name"></div>
            <div class="field"><label>Status</label>
                <select id="ed-status" name="status">
                    <option value="live">Live</option>
                    <option value="draft">Draft</option>
                    <option value="unread">Unread</option>
                </select>
            </div>
            <div class="field" style="grid-column:1 / -1"><label>Description</label><textarea id="ed-desc" name="description"></textarea></div>
            <div class="field" id="ed-extra" style="grid-column:1 / -1"></div>
            <div style="grid-column:1 / -1;display:flex;gap:8px;justify-content:flex-end">
                <button class="btn" type="button" id="ed-cancel">Cancel</button>
                <button class="btn primary" type="submit" id="ed-save">Save</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Toast -->
<div id="toast" style="position:fixed;bottom:20px;right:20px;padding:10px 14px;border-radius:12px;background:#111827;border:1px solid var(--border);display:none"></div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.background = type === 'error' ? 'var(--danger)' : 
                            type === 'success' ? 'var(--success)' : 'var(--panel)';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Global editor object
const editor = {
    close: function() {
        document.getElementById('editorModal').close();
    }
};

// Event listeners for editor
document.getElementById('ed-cancel').addEventListener('click', () => editor.close());
</script>

<?php include __DIR__ . '/admin-components/admin-footer.php';?>