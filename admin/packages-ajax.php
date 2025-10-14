[file name]: packages-ajax.php
[file content begin]
<?php
// packages-ajax.php

// Start output buffering with error handling
ob_start();

// Disable all error reporting to prevent any output
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Set proper headers for JSON response
header('Content-Type: application/json; charset=UTF-8');

// Function to send clean JSON response
function sendJsonResponse($success, $message = '', $data = null) {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    
    // Clear any existing output
    while (ob_get_level()) ob_end_clean();
    
    echo json_encode($response);
    exit();
}

try {
    // Include files - check if they exist first
    $dbConfigPath = __DIR__ . '/includes/db-config.php';
    $functionsPath = __DIR__ . '/functions/function.php';
    
    if (!file_exists($dbConfigPath) || !file_exists($functionsPath)) {
        throw new Exception('Required files not found');
    }
    
    include $dbConfigPath;
    include $functionsPath;

    // Check database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Check if user is admin
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Verify CSRF token for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    if (empty($action)) {
        throw new Exception('No action specified');
    }

    switch ($action) {
        case 'get_package':
            getPackage();
            break;
        case 'get_package_details':
            getPackageDetails();
            break;
        case 'bulk_update_status':
            bulkUpdateStatus();
            break;
        case 'bulk_delete':
            bulkDelete();
            break;
        case 'delete':
            deletePackage();
            break;
        case 'save':
            savePackage();
            break;
        default:
            throw new Exception('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    sendJsonResponse(false, $e->getMessage());
}

function getPackage() {
    global $conn;
    
    $packageId = $_GET['package_id'] ?? null;
    if (!$packageId) {
        throw new Exception('Package ID is required');
    }
    
    if (!is_numeric($packageId)) {
        throw new Exception('Invalid Package ID');
    }
    
    $stmt = $conn->prepare("SELECT * FROM tbl_packages WHERE package_id = ?");
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $packageId);
    
    if (!$stmt->execute()) {
        throw new Exception('Database query failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
    
    if ($package) {
        sendJsonResponse(true, '', $package);
    } else {
        throw new Exception('Package not found');
    }
}

function getPackageDetails() {
    global $conn;
    
    $packageId = $_GET['package_id'] ?? null;
    if (!$packageId) {
        throw new Exception('Package ID is required');
    }
    
    if (!is_numeric($packageId)) {
        throw new Exception('Invalid Package ID');
    }
    
    $stmt = $conn->prepare("SELECT * FROM tbl_packages WHERE package_id = ?");
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $packageId);
    
    if (!$stmt->execute()) {
        throw new Exception('Database query failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
    
    if ($package) {
        $html = '
        <div style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">
                <div>
                    <h3 style="margin: 0 0 8px 0; color: var(--text);">' . htmlspecialchars($package['package_name']) . '</h3>
                    <span class="badge" style="background: rgba(79, 70, 229, 0.12); color: var(--brand); padding: 4px 12px; border-radius: 12px; border: 1px solid rgba(79, 70, 229, 0.25); font-size: 12px;">
                        ' . htmlspecialchars($package['event_type']) . '
                    </span>
                </div>
                <span class="badge ' . ($package['status'] == 'active' ? 'live' : 'draft') . '" style="padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                    ' . ucfirst($package['status']) . '
                </span>
            </div>

            <div style="display: grid; gap: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                    <span style="font-weight: 600; color: var(--text);">Base Price:</span>
                    <span style="font-weight: 700; color: var(--ok); font-size: 16px;">₱' . number_format($package['base_price'], 2) . '</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                    <span style="font-weight: 600; color: var(--text);">Created Date:</span>
                    <span style="color: var(--muted);">' . date('M j, Y g:i A', strtotime($package['created_at'])) . '</span>
                </div>
            </div>

            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                <h4 style="margin: 0 0 12px 0; color: var(--text); font-size: 14px;">Description</h4>
                <div style="background: var(--panel-2); padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
                    <p style="margin: 0; color: var(--text); line-height: 1.6;">' . nl2br(htmlspecialchars($package['package_description'])) . '</p>
                </div>
            </div>
        </div>';
        
        sendJsonResponse(true, '', ['html' => $html]);
    } else {
        throw new Exception('Package not found');
    }
}

function bulkUpdateStatus() {
    global $conn;
    
    if (!isset($_POST['package_ids']) || !isset($_POST['status'])) {
        throw new Exception('Missing required parameters');
    }
    
    $packageIds = json_decode($_POST['package_ids']);
    $status = trim($_POST['status']);
    
    if (!is_array($packageIds) || empty($packageIds)) {
        throw new Exception('No packages selected');
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status');
    }
    
    // Validate all package IDs are numeric
    foreach ($packageIds as $id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid package ID');
        }
    }
    
    $placeholders = str_repeat('?,', count($packageIds) - 1) . '?';
    $stmt = $conn->prepare("UPDATE tbl_packages SET status = ? WHERE package_id IN ($placeholders)");
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $types = 's' . str_repeat('i', count($packageIds));
    $params = array_merge([$status], $packageIds);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Packages updated successfully');
    } else {
        throw new Exception('Failed to update packages: ' . $stmt->error);
    }
    
    $stmt->close();
}

function bulkDelete() {
    global $conn;
    
    if (!isset($_POST['package_ids'])) {
        throw new Exception('Missing package IDs');
    }
    
    $packageIds = json_decode($_POST['package_ids']);
    
    if (!is_array($packageIds) || empty($packageIds)) {
        throw new Exception('No packages selected');
    }
    
    // Validate all package IDs are numeric
    foreach ($packageIds as $id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid package ID');
        }
    }
    
    $placeholders = str_repeat('?,', count($packageIds) - 1) . '?';
    $stmt = $conn->prepare("DELETE FROM tbl_packages WHERE package_id IN ($placeholders)");
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $types = str_repeat('i', count($packageIds));
    $stmt->bind_param($types, ...$packageIds);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Packages deleted successfully');
    } else {
        throw new Exception('Failed to delete packages: ' . $stmt->error);
    }
    
    $stmt->close();
}

function deletePackage() {
    global $conn;
    
    $packageId = $_POST['package_id'] ?? null;
    
    if (!$packageId) {
        throw new Exception('Package ID is required');
    }
    
    if (!is_numeric($packageId)) {
        throw new Exception('Invalid Package ID');
    }
    
    $stmt = $conn->prepare("DELETE FROM tbl_packages WHERE package_id = ?");
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $packageId);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Package deleted successfully');
    } else {
        throw new Exception('Failed to delete package: ' . $stmt->error);
    }
    
    $stmt->close();
}

function savePackage() {
    global $conn;
    
    // Get form data
    $packageId = $_POST['package_id'] ?? null;
    $packageName = trim($_POST['package_name'] ?? '');
    $eventType = trim($_POST['event_type'] ?? '');
    $basePrice = floatval($_POST['base_price'] ?? 0);
    $description = trim($_POST['package_description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validate required fields
    if (empty($packageName)) {
        throw new Exception('Package name is required');
    }
    
    if (empty($eventType)) {
        throw new Exception('Event type is required');
    }
    
    if ($basePrice <= 0) {
        throw new Exception('Price must be greater than 0');
    }
    
    if (empty($description)) {
        throw new Exception('Description is required');
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status');
    }
    
    if ($packageId) {
        // Update existing package
        if (!is_numeric($packageId)) {
            throw new Exception('Invalid Package ID');
        }
        
        $stmt = $conn->prepare("
            UPDATE tbl_packages 
            SET package_name = ?, event_type = ?, base_price = ?, package_description = ?, 
                status = ?
            WHERE package_id = ?
        ");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("ssdssi", $packageName, $eventType, $basePrice, $description, $status, $packageId);
    } else {
        // Insert new package
        $stmt = $conn->prepare("
            INSERT INTO tbl_packages (package_name, event_type, base_price, package_description, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("ssdss", $packageName, $eventType, $basePrice, $description, $status);
    }
    
    if ($stmt->execute()) {
        $message = $packageId ? 'Package updated successfully' : 'Package created successfully';
        sendJsonResponse(true, $message);
    } else {
        throw new Exception('Failed to save package: ' . $stmt->error);
    }
    
    $stmt->close();
}
?>
[file content end]