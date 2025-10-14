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

// Fetch reviews data
$reviews = [];
$stats = [];

try {
    // Fetch review statistics
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_feedback");
    $stats['totalReviews'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_feedback WHERE status = 'pending'");
    $stats['pendingReviews'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_feedback WHERE status = 'approved'");
    $stats['approvedReviews'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_feedback WHERE status = 'rejected'");
    $stats['rejectedReviews'] = $result->fetch_assoc()['total'];
    
    // Calculate average rating
    $result = $conn->query("SELECT AVG(rating) as average FROM tbl_feedback WHERE status = 'approved'");
    $avgRating = $result->fetch_assoc()['average'];
    $stats['averageRating'] = $avgRating ? round($avgRating, 1) : 0;
    
    // Fetch all reviews with user information
    $stmt = $conn->prepare("
        SELECT f.*, u.first_name, u.last_name, u.email
        FROM tbl_feedback f
        LEFT JOIN tbl_users u ON f.user_id = u.user_id
        ORDER BY f.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
} catch (Exception $e) {
    $error = "Error loading reviews: " . $e->getMessage();
}
?>

<!-- REVIEWS MANAGEMENT -->
<section id="view-reviews" class="view">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; font-size: 24px; font-weight: 700;">Customer Reviews Management</h2>
        <div style="display: flex; gap: 8px;">
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

    <!-- Review Statistics -->
    <div class="cards" style="margin-bottom: 24px;">
        <div class="card">
            <h3>Total Reviews</h3>
            <div class="big"><?php echo htmlspecialchars($stats['totalReviews'] ?? 0); ?></div>
            <div class="muted">All customer reviews</div>
        </div>
        <div class="card">
            <h3>Pending Reviews</h3>
            <div class="big"><?php echo htmlspecialchars($stats['pendingReviews'] ?? 0); ?></div>
            <div class="muted">Awaiting moderation</div>
        </div>
        <div class="card">
            <h3>Approved Reviews</h3>
            <div class="big"><?php echo htmlspecialchars($stats['approvedReviews'] ?? 0); ?></div>
            <div class="muted">Published reviews</div>
        </div>
        <div class="card">
            <h3>Average Rating</h3>
            <div class="big"><?php echo htmlspecialchars($stats['averageRating'] ?? 0); ?>/5</div>
            <div class="muted">From approved reviews</div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="card filter-panel" style="margin-bottom: 24px; border-left: 4px solid var(--brand);">
        <div class="card-body">
            <h4 style="margin: 0 0 16px 0; color: var(--text); font-size: 16px;">Filter Reviews</h4>
            <div class="form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div class="field">
                    <label>Search</label>
                    <input type="text" id="searchReview" placeholder="Search by user or title..." style="width: 100%;">
                </div>
                <div class="field">
                    <label>Status</label>
                    <select id="filterStatus" style="width: 100%;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="field">
                    <label>Rating</label>
                    <select id="filterRating" style="width: 100%;">
                        <option value="">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="field" style="display: flex; align-items: flex-end;">
                    <button class="btn" onclick="resetFilters()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions-panel" id="bulkActionsPanel" style="display: none; margin-bottom: 16px; background: var(--warn); border-left: 4px solid var(--warn); border-radius: 12px; padding: 12px 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <span id="selectedCount" style="font-weight: 600; color: white;">0 reviews selected</span>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button class="btn ok" onclick="bulkUpdateStatus('approved')" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-check-circle"></i> Approve Selected
                </button>
                <button class="btn warn" onclick="bulkUpdateStatus('rejected')" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-x-circle"></i> Reject Selected
                </button>
                <button class="btn danger" onclick="bulkDelete()" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table id="reviewsTable" class="table" style="width: 100%; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th width="40" style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" style="cursor: pointer;">
                            </th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Review Details</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">User</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Rating</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Status</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Date</th>
                            <th width="220" style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                            <tr data-review-id="<?php echo $review['feedback_id']; ?>" style="transition: all 0.2s ease;">
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <input type="checkbox" class="review-checkbox" value="<?php echo $review['feedback_id']; ?>" onchange="updateBulkActions()" style="cursor: pointer;">
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="font-weight: 600; color: var(--text); margin-bottom: 4px;">
                                        <?php echo htmlspecialchars($review['title']); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                        <?php echo htmlspecialchars(substr($review['message'], 0, 100)); ?>
                                        <?php echo strlen($review['message']) > 100 ? '...' : ''; ?>
                                    </div>
                                    <?php if ($review['order_reference']): ?>
                                        <div style="color: var(--muted); font-size: 11px; margin-top: 4px;">
                                            Order: <?php echo htmlspecialchars($review['order_reference']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="font-weight: 500; margin-bottom: 2px;">
                                        <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 12px;">
                                        <?php echo htmlspecialchars($review['email']); ?>
                                    </div>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span style="color: <?php echo $i <= $review['rating'] ? '#fbbf24' : '#d1d5db'; ?>; font-size: 14px;">
                                                ★
                                            </span>
                                        <?php endfor; ?>
                                        <span style="color: var(--muted); font-size: 12px; margin-left: 4px;">
                                            (<?php echo $review['rating']; ?>/5)
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <span class="status <?php 
                                        echo $review['status'] == 'approved' ? 'live' : 
                                             ($review['status'] == 'pending' ? 'draft' : 'cancelled'); 
                                    ?>">
                                        <?php echo ucfirst($review['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                    <div style="color: var(--muted); font-size: 12px;">
                                        <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                    </div>
                                    <div style="color: var(--muted); font-size: 11px;">
                                        <?php echo date('g:i A', strtotime($review['created_at'])); ?>
                                    </div>
                                </td>
                                <td style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                        <button class="btn" onclick="viewReviewDetails(<?php echo $review['feedback_id']; ?>)" title="View Details" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <?php if ($review['status'] == 'pending'): ?>
                                            <button class="btn ok" onclick="approveReview(<?php echo $review['feedback_id']; ?>)" title="Approve" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                                <i class="bi bi-check"></i> Approve
                                            </button>
                                            <button class="btn danger" onclick="rejectReview(<?php echo $review['feedback_id']; ?>)" title="Reject" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                                <i class="bi bi-x"></i> Reject
                                            </button>
                                        <?php elseif ($review['status'] == 'approved'): ?>
                                            <button class="btn warn" onclick="rejectReview(<?php echo $review['feedback_id']; ?>)" title="Reject" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                                <i class="bi bi-x"></i> Reject
                                            </button>
                                        <?php else: ?>
                                            <button class="btn ok" onclick="approveReview(<?php echo $review['feedback_id']; ?>)" title="Approve" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                                <i class="bi bi-check"></i> Approve
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn danger" onclick="deleteReview(<?php echo $review['feedback_id']; ?>, '<?php echo htmlspecialchars(addslashes($review['title'])); ?>')" title="Delete" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="padding: 40px 16px; text-align: center; color: var(--muted);">
                                    <div style="font-size: 16px; margin-bottom: 8px;">No reviews found</div>
                                    <div style="font-size: 14px;">Customer reviews will appear here once submitted.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- REVIEW DETAILS MODAL -->
<dialog id="reviewDetailsModal">
    <div class="modal-header">
        <strong style="font-size: 18px;">Review Details</strong>
        <button class="btn" onclick="closeReviewDetailsModal()" style="padding: 6px 10px;">✕</button>
    </div>
    <div class="modal-body" id="reviewDetailsContent" style="max-height: 70vh; overflow-y: auto; padding: 20px;">
        <!-- Details will be loaded via AJAX -->
    </div>
</dialog>

<!-- Loading Overlay -->
<div id="globalSpinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: var(--panel); padding: 24px; border-radius: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 12px;">
        <div style="width: 24px; height: 24px; border: 2px solid var(--border); border-top: 2px solid var(--brand); border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <span>Loading...</span>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.table-responsive {
    overflow-x: auto;
}

#reviewsTable tbody tr:hover {
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

.status.cancelled {
    background: rgba(239, 68, 68, 0.12);
    border-color: rgba(239, 68, 68, 0.25);
    color: var(--danger);
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
    
    #reviewsTable {
        font-size: 14px;
    }
    
    #reviewsTable td {
        padding: 12px 8px !important;
    }
    
    .bulk-actions-panel > div {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start !important;
    }
    
    #reviewsTable th:nth-child(7),
    #reviewsTable td:nth-child(7) {
        min-width: 200px;
    }
}

/* Custom scrollbar for modal */
#reviewDetailsContent::-webkit-scrollbar {
    width: 6px;
}

#reviewDetailsContent::-webkit-scrollbar-track {
    background: var(--panel-2);
    border-radius: 3px;
}

#reviewDetailsContent::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 3px;
}

#reviewDetailsContent::-webkit-scrollbar-thumb:hover {
    background: var(--muted);
}

/* Button spacing fixes */
.btn {
    white-space: nowrap;
}

/* Table cell spacing */
#reviewsTable td {
    vertical-align: top;
}
</style>

<script>
// Review Modal Functions
function closeReviewDetailsModal() {
    document.getElementById('reviewDetailsModal').close();
}

// View review details
async function viewReviewDetails(reviewId) {
    showLoading();
    try {
        const response = await fetch(`reviews-ajax.php?action=get_review_details&review_id=${reviewId}`);
        const reviewData = await response.json();
        
        if (reviewData.success) {
            document.getElementById('reviewDetailsContent').innerHTML = reviewData.html;
            document.getElementById('reviewDetailsModal').showModal();
        } else {
            showToast('Error loading review details: ' + (reviewData.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Bulk Actions Functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.review-checkbox:not([style*="display: none"])');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const selected = document.querySelectorAll('.review-checkbox:checked');
    const bulkPanel = document.getElementById('bulkActionsPanel');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = `${selected.length} review(s) selected`;
    bulkPanel.style.display = selected.length > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const totalVisibleCheckboxes = document.querySelectorAll('.review-checkbox:not([style*="display: none"])').length;
    const selectAll = document.getElementById('selectAll');
    selectAll.checked = selected.length === totalVisibleCheckboxes && totalVisibleCheckboxes > 0;
    selectAll.indeterminate = selected.length > 0 && selected.length < totalVisibleCheckboxes;
}

async function bulkUpdateStatus(status) {
    const selected = Array.from(document.querySelectorAll('.review-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        showToast('Please select at least one review', 'warning');
        return;
    }
    
    const action = status === 'approved' ? 'approve' : 'reject';
    if (!confirm(`Are you sure you want to ${action} ${selected.length} review(s)?`)) {
        return;
    }
    
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'bulk_update_status');
        formData.append('review_ids', JSON.stringify(selected));
        formData.append('status', status);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('reviews-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(`Successfully ${action}d ${selected.length} review(s)`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error updating reviews: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

function bulkDelete() {
    const selected = Array.from(document.querySelectorAll('.review-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        showToast('Please select at least one review', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selected.length} review(s)? This action cannot be undone.`)) {
        performBulkDelete(selected);
    }
}

async function performBulkDelete(reviewIds) {
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'bulk_delete');
        formData.append('review_ids', JSON.stringify(reviewIds));
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('reviews-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(`Successfully deleted ${reviewIds.length} review(s)`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error deleting reviews: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Single Review Actions
function approveReview(reviewId) {
    if (confirm('Are you sure you want to approve this review?')) {
        performSingleStatusUpdate(reviewId, 'approved');
    }
}

function rejectReview(reviewId) {
    if (confirm('Are you sure you want to reject this review?')) {
        performSingleStatusUpdate(reviewId, 'rejected');
    }
}

async function performSingleStatusUpdate(reviewId, status) {
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('review_id', reviewId);
        formData.append('status', status);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('reviews-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            const action = status === 'approved' ? 'approved' : 'rejected';
            showToast(`Review ${action} successfully`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error updating review: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

function deleteReview(reviewId, reviewTitle) {
    if (confirm(`Are you sure you want to delete "${reviewTitle}"? This action cannot be undone.`)) {
        performSingleDelete(reviewId);
    }
}

async function performSingleDelete(reviewId) {
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('review_id', reviewId);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('reviews-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Review deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error deleting review: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Export functionality
function exportReviews() {
    showToast('Export feature would be implemented here', 'info');
}

// Utility functions
function resetFilters() {
    document.getElementById('searchReview').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterRating').value = '';
    
    // Reset table filtering
    const rows = document.querySelectorAll('#reviewsTable tbody tr');
    rows.forEach(row => row.style.display = '');
    
    // Reset bulk actions
    updateBulkActions();
    
    showToast('Filters reset successfully', 'info');
}

function showLoading() {
    document.getElementById('globalSpinner').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('globalSpinner').style.display = 'none';
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

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initialize table filtering
document.addEventListener('DOMContentLoaded', function() {
    // Simple table filtering
    document.getElementById('searchReview').addEventListener('input', function() {
        filterTable();
    });
    
    document.getElementById('filterStatus').addEventListener('change', function() {
        filterTable();
    });
    
    document.getElementById('filterRating').addEventListener('change', function() {
        filterTable();
    });
});

function filterTable() {
    const searchFilter = document.getElementById('searchReview').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value;
    const ratingFilter = document.getElementById('filterRating').value;
    
    const rows = document.querySelectorAll('#reviewsTable tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const title = row.cells[1].querySelector('div:first-child').textContent.toLowerCase();
        const message = row.cells[1].querySelector('div:nth-child(2)').textContent.toLowerCase();
        const user = row.cells[2].textContent.toLowerCase();
        const status = row.cells[4].textContent.toLowerCase();
        const ratingText = row.cells[3].querySelector('span:last-child').textContent;
        const rating = ratingText.match(/\d/)?.[0];
        
        const searchMatch = title.includes(searchFilter) || message.includes(searchFilter) || user.includes(searchFilter);
        const statusMatch = !statusFilter || status === statusFilter.toLowerCase();
        const ratingMatch = !ratingFilter || rating === ratingFilter;
        
        const shouldShow = searchMatch && statusMatch && ratingMatch;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) visibleCount++;
    });
    
    // Update bulk actions after filtering
    updateBulkActions();
}
</script>

<?php include __DIR__ . '/admin-components/admin-footer.php';?>