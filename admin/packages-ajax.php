<?php
// packages-ajax.php
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
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getPackage() {
    global $conn;
    
    $packageId = $_GET['package_id'] ?? null;
    if (!$packageId) {
        throw new Exception('Package ID is required');
    }
    
    $stmt = $conn->prepare("SELECT * FROM tbl_packages WHERE package_id = ?");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
    
    if ($package) {
        echo json_encode(['success' => true, 'data' => $package]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Package not found']);
    }
}

function getPackageDetails() {
    global $conn;
    
    $packageId = $_GET['package_id'] ?? null;
    if (!$packageId) {
        throw new Exception('Package ID is required');
    }
    
    $stmt = $conn->prepare("SELECT * FROM tbl_packages WHERE package_id = ?");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
    
    if ($package) {
        $html = '
        <div class="row">
            <div class="col-md-6">
                <strong>Package Name:</strong>
            </div>
            <div class="col-md-6">
                ' . htmlspecialchars($package['package_name']) . '
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Event Type:</strong>
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary">' . htmlspecialchars($package['event_type']) . '</span>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Base Price:</strong>
            </div>
            <div class="col-md-6">
                <strong class="text-success">₱' . number_format($package['base_price'], 2) . '</strong>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Status:</strong>
            </div>
            <div class="col-md-6">
                <span class="badge ' . ($package['status'] == 'active' ? 'bg-success' : 'bg-secondary') . '">
                    ' . ucfirst($package['status']) . '
                </span>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Created:</strong>
            </div>
            <div class="col-md-6">
                ' . date('M j, Y g:i A', strtotime($package['created_at'])) . '
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <strong>Description:</strong>
                <p class="mt-1">' . nl2br(htmlspecialchars($package['package_description'])) . '</p>
            </div>
        </div>';
        
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Package not found']);
    }
}

function bulkUpdateStatus() {
    global $conn;
    
    $packageIds = json_decode($_POST['package_ids']);
    $status = $_POST['status'];
    
    if (empty($packageIds)) {
        throw new Exception('No packages selected');
    }
    
    $placeholders = str_repeat('?,', count($packageIds) - 1) . '?';
    $stmt = $conn->prepare("UPDATE tbl_packages SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE package_id IN ($placeholders)");
    
    $types = 's' . str_repeat('i', count($packageIds));
    $params = array_merge([$status], $packageIds);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Packages updated successfully']);
    } else {
        throw new Exception('Failed to update packages: ' . $stmt->error);
    }
    
    $stmt->close();
}

function bulkDelete() {
    global $conn;
    
    $packageIds = json_decode($_POST['package_ids']);
    
    if (empty($packageIds)) {
        throw new Exception('No packages selected');
    }
    
    $placeholders = str_repeat('?,', count($packageIds) - 1) . '?';
    $stmt = $conn->prepare("DELETE FROM tbl_packages WHERE package_id IN ($placeholders)");
    
    $types = str_repeat('i', count($packageIds));
    $stmt->bind_param($types, ...$packageIds);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Packages deleted successfully']);
    } else {
        throw new Exception('Failed to delete packages: ' . $stmt->error);
    }
    
    $stmt->close();
}

function deletePackage() {
    global $conn;
    
    $packageId = $_POST['package_id'];
    
    if (empty($packageId)) {
        throw new Exception('Package ID is required');
    }
    
    $stmt = $conn->prepare("DELETE FROM tbl_packages WHERE package_id = ?");
    $stmt->bind_param("i", $packageId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Package deleted successfully']);
    } else {
        throw new Exception('Failed to delete package: ' . $stmt->error);
    }
    
    $stmt->close();
}

function savePackage() {
    global $conn;
    
    // Get form data
    $packageId = $_POST['package_id'] ?? null;
    $packageName = trim($_POST['package_name']);
    $eventType = trim($_POST['event_type']);
    $basePrice = floatval($_POST['base_price']);
    $description = trim($_POST['package_description']);
    $status = $_POST['status'] ?? 'active';
    
    // Validate required fields
    if (empty($packageName) || empty($eventType) || empty($description) || $basePrice <= 0) {
        throw new Exception('All fields are required and price must be greater than 0');
    }
    
    if ($packageId) {
        // Update existing package
        $stmt = $conn->prepare("
            UPDATE tbl_packages 
            SET package_name = ?, event_type = ?, base_price = ?, package_description = ?, 
                status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE package_id = ?
        ");
        $stmt->bind_param("ssdssi", $packageName, $eventType, $basePrice, $description, $status, $packageId);
    } else {
        // Insert new package
        $stmt = $conn->prepare("
            INSERT INTO tbl_packages (package_name, event_type, base_price, package_description, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdss", $packageName, $eventType, $basePrice, $description, $status);
    }
    
    if ($stmt->execute()) {
        $message = $packageId ? 'Package updated successfully' : 'Package created successfully';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        throw new Exception('Failed to save package: ' . $stmt->error);
    }
    
    $stmt->close();
}
?>