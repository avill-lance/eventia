<?php
session_start();
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: packages.php?error=Invalid CSRF token');
    exit();
}

// Handle different actions
$action = $_POST['action'] ?? 'save';

try {
    if ($action === 'delete') {
        handleDeletePackage();
    } else {
        handleSavePackage();
    }
} catch (Exception $e) {
    header('Location: packages.php?error=' . urlencode($e->getMessage()));
    exit();
}

function handleSavePackage() {
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
        header('Location: packages.php?success=' . urlencode($message));
    } else {
        throw new Exception('Failed to save package: ' . $stmt->error);
    }
    
    $stmt->close();
}

function handleDeletePackage() {
    global $conn;
    
    $packageId = $_POST['package_id'];
    
    if (empty($packageId)) {
        throw new Exception('Package ID is required');
    }
    
    // Delete the package
    $stmt = $conn->prepare("DELETE FROM tbl_packages WHERE package_id = ?");
    $stmt->bind_param("i", $packageId);
    
    if ($stmt->execute()) {
        header('Location: packages.php?success=Package deleted successfully');
    } else {
        throw new Exception('Failed to delete package: ' . $stmt->error);
    }
    
    $stmt->close();
}
?>