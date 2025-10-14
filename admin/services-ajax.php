<?php
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

// Check if required files exist before including
$dbConfigPath = __DIR__ . '/includes/db-config.php';
$authPath = __DIR__ . '/includes/auth.php';

if (!file_exists($dbConfigPath) || !file_exists($authPath)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Required files not found']);
    exit;
}

include $dbConfigPath;
include $authPath;

// Verify admin authentication
try {
    requireAuth();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication failed: ' . $e->getMessage()]);
    exit;
}

// Set JSON header immediately
header('Content-Type: application/json');

// Verify we have a valid action
$action = $_GET['action'] ?? '';

if (empty($action)) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

try {
    switch ($action) {
        case 'save':
            // Verify CSRF token for save action
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }
            saveService();
            break;
        case 'load':
            loadService();
            break;
        case 'delete':
            // Verify CSRF token for delete action
            if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }
            deleteService();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (Exception $e) {
    error_log("Service AJAX Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function saveService() {
    global $conn;
    
    // Validate required fields
    if (!isset($_POST['service_name']) || empty(trim($_POST['service_name']))) {
        echo json_encode(['success' => false, 'message' => 'Service name is required']);
        return;
    }
    
    $serviceId = $_POST['service_id'] ?? null;
    $serviceName = trim($_POST['service_name']);
    $category = trim($_POST['category'] ?? '');
    $basePrice = floatval($_POST['base_price'] ?? 0);
    $description = trim($_POST['service_description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $customizable = isset($_POST['customizable']) ? 1 : 0;
    
    if ($basePrice < 0) {
        echo json_encode(['success' => false, 'message' => 'Base price must be a positive number']);
        return;
    }
    
    // Build customization options JSON
    $customizationOptions = buildCustomizationOptions();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        if ($serviceId) {
            // Update existing service
            $stmt = $conn->prepare("
                UPDATE tbl_services 
                SET service_name = ?, service_description = ?, base_price = ?, 
                    category = ?, customizable = ?, customization_options = ?, status = ?
                WHERE service_id = ?
            ");
            if (!$stmt) {
                throw new Exception('Database error: ' . $conn->error);
            }
            $stmt->bind_param("ssdssssi", $serviceName, $description, $basePrice, $category, 
                             $customizable, $customizationOptions, $status, $serviceId);
        } else {
            // Insert new service
            $stmt = $conn->prepare("
                INSERT INTO tbl_services (service_name, service_description, base_price, 
                                        category, customizable, customization_options, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) {
                throw new Exception('Database error: ' . $conn->error);
            }
            $stmt->bind_param("ssdssss", $serviceName, $description, $basePrice, $category, 
                             $customizable, $customizationOptions, $status);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Error saving service: ' . $stmt->error);
        }
        
        $serviceId = $serviceId ?: $stmt->insert_id;
        $stmt->close();
        
        // Save service details
        saveServiceDetails($serviceId);
        
        // Save service features
        saveServiceFeatures($serviceId);
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Service saved successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function buildCustomizationOptions() {
    $options = ['options' => []];
    
    $customizationData = $_POST['customization'] ?? [];
    
    if (empty($customizationData)) {
        return '[]';
    }
    
    foreach ($customizationData as $optionId => $optionData) {
        // Skip if name is empty
        if (empty(trim($optionData['name'] ?? ''))) {
            continue;
        }
        
        $optionName = trim($optionData['name']);
        $optionType = $optionData['type'] ?? 'number';
        
        $optionConfig = [
            'type' => $optionType,
            'price' => floatval($optionData['price'] ?? 0),
            'price_type' => $optionData['price_type'] ?? 'fixed'
        ];
        
        // Add type-specific configurations
        if ($optionType === 'number') {
            $optionConfig['min'] = isset($optionData['min']) ? intval($optionData['min']) : 1;
            $optionConfig['max'] = isset($optionData['max']) ? intval($optionData['max']) : 100;
        } elseif ($optionType === 'select' && !empty($optionData['options'])) {
            $choices = array_map('trim', explode(',', $optionData['options']));
            $optionConfig['choices'] = array_filter($choices); // Remove empty values
        }
        
        $options['options'][$optionName] = $optionConfig;
    }
    
    return empty($options['options']) ? '[]' : json_encode($options, JSON_UNESCAPED_UNICODE);
}

function saveServiceDetails($serviceId) {
    global $conn;
    
    // Delete existing details
    $stmt = $conn->prepare("DELETE FROM tbl_service_details WHERE service_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insert new details
    foreach ($_POST['details'] ?? [] as $detailId => $detailData) {
        $detailName = trim($detailData['detail_name'] ?? '');
        $priceMin = floatval($detailData['price_min'] ?? 0);
        $priceMax = floatval($detailData['price_max'] ?? 0);
        
        if (!empty($detailName)) {
            $stmt = $conn->prepare("
                INSERT INTO tbl_service_details (service_id, detail_name, price_min, price_max)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param("isdd", $serviceId, $detailName, $priceMin, $priceMax);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

function saveServiceFeatures($serviceId) {
    global $conn;
    
    // Delete existing features
    $stmt = $conn->prepare("DELETE FROM tbl_service_features WHERE service_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insert new features
    foreach ($_POST['features'] ?? [] as $featureId => $featureData) {
        $featureName = trim($featureData['feature_name'] ?? '');
        
        if (!empty($featureName)) {
            $stmt = $conn->prepare("
                INSERT INTO tbl_service_features (service_id, feature_name)
                VALUES (?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param("is", $serviceId, $featureName);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

function loadService() {
    global $conn;
    
    $serviceId = intval($_GET['service_id'] ?? 0);
    
    if ($serviceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
        return;
    }
    
    error_log("Loading service ID: " . $serviceId);
    
    // Load service basic info
    $stmt = $conn->prepare("SELECT * FROM tbl_services WHERE service_id = ?");
    if (!$stmt) {
        error_log("Database error in prepare: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $serviceId);
    
    if (!$stmt->execute()) {
        error_log("Error executing query: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error executing query: ' . $stmt->error]);
        $stmt->close();
        return;
    }
    
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $stmt->close();
    
    if (!$service) {
        error_log("Service not found for ID: " . $serviceId);
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        return;
    }
    
    error_log("Service found: " . $service['service_name']);
    
    // Load service details
    $details = [];
    $stmt = $conn->prepare("SELECT * FROM tbl_service_details WHERE service_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $detailsResult = $stmt->get_result();
        $details = $detailsResult->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        error_log("Error preparing details query: " . $conn->error);
    }
    
    // Load service features
    $features = [];
    $stmt = $conn->prepare("SELECT * FROM tbl_service_features WHERE service_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $featuresResult = $stmt->get_result();
        $features = $featuresResult->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        error_log("Error preparing features query: " . $conn->error);
    }
    
    // Parse customization options
    $customizationOptions = [];
    if (!empty($service['customization_options'])) {
        $customizationOptions = json_decode($service['customization_options'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error for customization_options: " . json_last_error_msg());
            $customizationOptions = [];
        }
    }
    
    $service['customization_options'] = $customizationOptions;
    $service['details'] = $details;
    $service['features'] = $features;
    
    error_log("Service data prepared successfully with " . count($details) . " details and " . count($features) . " features");
    
    echo json_encode(['success' => true, 'service' => $service]);
}

function deleteService() {
    global $conn;
    
    $serviceId = intval($_GET['service_id'] ?? 0);
    
    if ($serviceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
        return;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete related records first
        $stmt = $conn->prepare("DELETE FROM tbl_service_details WHERE service_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $stmt->close();
        }
        
        $stmt = $conn->prepare("DELETE FROM tbl_service_features WHERE service_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $stmt->close();
        }
        
        // Check if service is used in any bookings
        $bookingCount = 0;
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_booking_services WHERE service_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $result = $stmt->get_result();
            $bookingCount = $result->fetch_assoc()['count'];
            $stmt->close();
        }
        
        if ($bookingCount > 0) {
            // Instead of deleting, set to inactive
            $stmt = $conn->prepare("UPDATE tbl_services SET status = 'inactive' WHERE service_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $serviceId);
                $stmt->execute();
                $stmt->close();
            }
            $message = "Service has been deactivated because it's used in existing bookings";
        } else {
            // Delete the service
            $stmt = $conn->prepare("DELETE FROM tbl_services WHERE service_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $serviceId);
                $stmt->execute();
                $stmt->close();
            }
            $message = "Service deleted successfully";
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error deleting service: ' . $e->getMessage()]);
    }
}
?>