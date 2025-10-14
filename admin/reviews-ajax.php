<?php
// reviews-ajax.php
session_start();
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_review_details':
            getReviewDetails();
            break;
        case 'bulk_update_status':
            bulkUpdateStatus();
            break;
        case 'bulk_delete':
            bulkDelete();
            break;
        case 'update_status':
            updateStatus();
            break;
        case 'delete':
            deleteReview();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getReviewDetails() {
    global $conn;
    
    $reviewId = $_GET['review_id'] ?? null;
    if (!$reviewId) {
        throw new Exception('Review ID is required');
    }
    
    $stmt = $conn->prepare("
        SELECT f.*, u.first_name, u.last_name, u.email, u.phone
        FROM tbl_feedback f
        LEFT JOIN tbl_users u ON f.user_id = u.user_id
        WHERE f.feedback_id = ?
    ");
    $stmt->bind_param("i", $reviewId);
    $stmt->execute();
    $result = $stmt->get_result();
    $review = $result->fetch_assoc();
    $stmt->close();
    
    if ($review) {
        $html = '
        <div class="review-details">
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>Review Title:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    ' . htmlspecialchars($review['title']) . '
                </div>
            </div>
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>User:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    ' . htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) . '<br>
                    <small style="color: var(--muted);">' . htmlspecialchars($review['email']) . '</small>
                </div>
            </div>
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>Rating:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    <div style="display: flex; align-items: center; gap: 4px;">';
        
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<span style="color: ' . ($i <= $review['rating'] ? '#fbbf24' : '#d1d5db') . '; font-size: 18px;">★</span>';
        }
        
        $html .= '
                        <span style="color: var(--muted); margin-left: 8px;">(' . $review['rating'] . '/5)</span>
                    </div>
                </div>
            </div>
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>Status:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    <span class="status ' . ($review['status'] == 'approved' ? 'live' : ($review['status'] == 'pending' ? 'draft' : 'cancelled')) . '">
                        ' . ucfirst($review['status']) . '
                    </span>
                </div>
            </div>';
        
        if ($review['order_reference']) {
            $html .= '
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>Order Reference:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    ' . htmlspecialchars($review['order_reference']) . '
                </div>
            </div>';
        }
        
        $html .= '
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>Message:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    <div style="background: var(--panel-2); padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
                        ' . nl2br(htmlspecialchars($review['message'])) . '
                    </div>
                </div>
            </div>
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>Submitted:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    ' . date('M j, Y g:i A', strtotime($review['created_at'])) . '
                </div>
            </div>';
        
        if ($review['permission_granted']) {
            $html .= '
            <div class="row" style="display: flex; margin-bottom: 16px;">
                <div class="col" style="flex: 1;">
                    <strong>Permission:</strong>
                </div>
                <div class="col" style="flex: 2;">
                    <span style="color: var(--ok);">✓ User granted permission to publish</span>
                </div>
            </div>';
        }
        
        $html .= '</div>';
        
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Review not found']);
    }
}

function bulkUpdateStatus() {
    global $conn;
    
    $reviewIds = json_decode($_POST['review_ids']);
    $status = $_POST['status'];
    
    if (empty($reviewIds)) {
        throw new Exception('No reviews selected');
    }
    
    $placeholders = str_repeat('?,', count($reviewIds) - 1) . '?';
    $stmt = $conn->prepare("UPDATE tbl_feedback SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE feedback_id IN ($placeholders)");
    
    $types = 's' . str_repeat('i', count($reviewIds));
    $params = array_merge([$status], $reviewIds);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reviews updated successfully']);
    } else {
        throw new Exception('Failed to update reviews: ' . $stmt->error);
    }
    
    $stmt->close();
}

function bulkDelete() {
    global $conn;
    
    $reviewIds = json_decode($_POST['review_ids']);
    
    if (empty($reviewIds)) {
        throw new Exception('No reviews selected');
    }
    
    $placeholders = str_repeat('?,', count($reviewIds) - 1) . '?';
    $stmt = $conn->prepare("DELETE FROM tbl_feedback WHERE feedback_id IN ($placeholders)");
    
    $types = str_repeat('i', count($reviewIds));
    $stmt->bind_param($types, ...$reviewIds);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reviews deleted successfully']);
    } else {
        throw new Exception('Failed to delete reviews: ' . $stmt->error);
    }
    
    $stmt->close();
}

function updateStatus() {
    global $conn;
    
    $reviewId = $_POST['review_id'];
    $status = $_POST['status'];
    
    if (empty($reviewId)) {
        throw new Exception('Review ID is required');
    }
    
    $stmt = $conn->prepare("UPDATE tbl_feedback SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE feedback_id = ?");
    $stmt->bind_param("si", $status, $reviewId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review status updated successfully']);
    } else {
        throw new Exception('Failed to update review status: ' . $stmt->error);
    }
    
    $stmt->close();
}

function deleteReview() {
    global $conn;
    
    $reviewId = $_POST['review_id'];
    
    if (empty($reviewId)) {
        throw new Exception('Review ID is required');
    }
    
    $stmt = $conn->prepare("DELETE FROM tbl_feedback WHERE feedback_id = ?");
    $stmt->bind_param("i", $reviewId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
    } else {
        throw new Exception('Failed to delete review: ' . $stmt->error);
    }
    
    $stmt->close();
}
?>