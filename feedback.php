<?php 
include __DIR__."/components/header.php";

// ### Establish Database Connection ###
include __DIR__ . "/database/config.php";

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_message = '';
$error_message = '';

// Process feedback form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Security token validation failed.";
    } else {
        $feedback_type = htmlspecialchars($_POST['feedbackType'] ?? '');
        $order_reference = htmlspecialchars($_POST['orderReference'] ?? '');
        $rating = intval($_POST['rating'] ?? 5);
        $title = htmlspecialchars($_POST['feedbackTitle'] ?? '');
        $message = htmlspecialchars($_POST['feedbackText'] ?? '');
        $permission_granted = isset($_POST['permission']) ? 1 : 0;
        $user_id = $_SESSION['id'] ?? 0;

        // Validation
        if (empty($feedback_type) || empty($title) || empty($message) || $user_id == 0) {
            $error_message = "Please fill in all required fields.";
        } elseif ($rating < 1 || $rating > 5) {
            $error_message = "Please select a valid rating.";
        } else {
            // Handle file upload
            $photos = null;
            $photo_names = null;
            
            if (isset($_FILES['photoUpload']) && $_FILES['photoUpload']['error'][0] != 4) {
                $uploaded_files = $_FILES['photoUpload'];
                $valid_files = [];
                $file_names = [];
                
                foreach ($uploaded_files['name'] as $index => $name) {
                    if ($uploaded_files['error'][$index] === UPLOAD_ERR_OK) {
                        // Check file size (5MB limit)
                        if ($uploaded_files['size'][$index] > 5 * 1024 * 1024) {
                            $error_message = "File '{$name}' exceeds 5MB size limit.";
                            break;
                        }
                        
                        // Check file type
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        $file_type = mime_content_type($uploaded_files['tmp_name'][$index]);
                        if (!in_array($file_type, $allowed_types)) {
                            $error_message = "File '{$name}' is not a valid image type.";
                            break;
                        }
                        
                        // Read file content
                        $file_content = file_get_contents($uploaded_files['tmp_name'][$index]);
                        if ($file_content !== false) {
                            $valid_files[] = $file_content;
                            $file_names[] = $name;
                        }
                    }
                }
                
                if (empty($error_message)) {
                    $photos = !empty($valid_files) ? serialize($valid_files) : null;
                    $photo_names = !empty($file_names) ? serialize($file_names) : null;
                }
            }
            
            if (empty($error_message)) {
                // Insert into database using prepared statement
                $stmt = $conn->prepare("INSERT INTO tbl_feedback (user_id, feedback_type, order_reference, rating, title, message, photos, photo_names, permission_granted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issiisssi", $user_id, $feedback_type, $order_reference, $rating, $title, $message, $photos, $photo_names, $permission_granted);
                
                if ($stmt->execute()) {
                    $success_message = "Thank you for your feedback! Your submission has been received.";
                    // Clear form fields
                    $_POST = array();
                } else {
                    $error_message = "Sorry, there was an error submitting your feedback. Please try again.";
                }
                $stmt->close();
            }
        }
    }
}

// Fetch recent feedback for display - UPDATED TO LIMIT 5
$recent_feedback = [];
$recent_stmt = $conn->prepare("
    SELECT f.*, u.first_name, u.last_name 
    FROM tbl_feedback f 
    JOIN tbl_users u ON f.user_id = u.user_id 
    WHERE f.status = 'approved' 
    ORDER BY f.created_at DESC 
    LIMIT 5
");
if ($recent_stmt->execute()) {
    $recent_result = $recent_stmt->get_result();
    while ($row = $recent_result->fetch_assoc()) {
        $recent_feedback[] = $row;
    }
}
$recent_stmt->close();
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Share Your Experience</h1>
        <p class="lead mb-4">Your feedback helps us improve our services</p>
    </div>
</div>

<!-- Feedback Content -->
<div class="container my-5">
    <!-- Success/Error Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Feedback Form -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Submit Feedback</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="feedbackType" class="form-label">Feedback Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="feedbackType" name="feedbackType" required>
                                <option value="" selected disabled>Select feedback type</option>
                                <option value="Event Service" <?php echo isset($_POST['feedbackType']) && $_POST['feedbackType'] == 'Event Service' ? 'selected' : ''; ?>>Event Service</option>
                                <option value="Product Review" <?php echo isset($_POST['feedbackType']) && $_POST['feedbackType'] == 'Product Review' ? 'selected' : ''; ?>>Product Review</option>
                                <option value="Website Experience" <?php echo isset($_POST['feedbackType']) && $_POST['feedbackType'] == 'Website Experience' ? 'selected' : ''; ?>>Website Experience</option>
                                <option value="Customer Support" <?php echo isset($_POST['feedbackType']) && $_POST['feedbackType'] == 'Customer Support' ? 'selected' : ''; ?>>Customer Support</option>
                                <option value="Other" <?php echo isset($_POST['feedbackType']) && $_POST['feedbackType'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="orderReference" class="form-label">Order Reference (Optional)</label>
                            <input type="text" class="form-control" id="orderReference" name="orderReference" 
                                   value="<?php echo isset($_POST['orderReference']) ? htmlspecialchars($_POST['orderReference']) : ''; ?>" 
                                   placeholder="If applicable">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Overall Rating <span class="text-danger">*</span></label>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating<?php echo $i; ?>" 
                                               value="<?php echo $i; ?>" 
                                               <?php echo (isset($_POST['rating']) && $_POST['rating'] == $i) || (!isset($_POST['rating']) && $i == 5) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="rating<?php echo $i; ?>"><?php echo $i; ?></label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="feedbackTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="feedbackTitle" name="feedbackTitle" 
                                   value="<?php echo isset($_POST['feedbackTitle']) ? htmlspecialchars($_POST['feedbackTitle']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="feedbackText" class="form-label">Your Feedback <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="feedbackText" name="feedbackText" rows="5" required><?php echo isset($_POST['feedbackText']) ? htmlspecialchars($_POST['feedbackText']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="photoUpload" class="form-label">Upload Photos (Optional)</label>
                            <input type="file" class="form-control" id="photoUpload" name="photoUpload[]" multiple accept="image/*">
                            <div class="form-text">You can upload up to 5 photos. Maximum file size: 5MB each. Accepted formats: JPG, PNG, GIF, WebP.</div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="permission" name="permission" 
                                   <?php echo isset($_POST['permission']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="permission">
                                I give permission to Eventia to use my feedback and photos for marketing purposes
                            </label>
                        </div>
                        
                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recent Feedback</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_feedback)): ?>
                        <p class="text-muted">No feedback available yet. Be the first to share your experience!</p>
                    <?php else: ?>
                        <?php foreach ($recent_feedback as $index => $feedback): ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(substr($feedback['first_name'], 0, 1) . substr($feedback['last_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?></h6>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $feedback['rating'] ? '-fill' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-0">"<?php echo htmlspecialchars($feedback['message']); ?>"</p>
                                <?php if ($index < count($recent_feedback) - 1): ?>
                                    <hr>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Why Your Feedback Matters</h5>
                    <p class="card-text">We value your opinion and use your feedback to:</p>
                    <ul class="mb-0">
                        <li>Improve our services and products</li>
                        <li>Train our team members</li>
                        <li>Develop new features and offerings</li>
                        <li>Ensure customer satisfaction</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__."/components/footer.php"; ?>