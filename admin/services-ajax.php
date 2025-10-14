<?php
session_start();
include __DIR__ . '/../includes/db-config.php';
include __DIR__ . '/../includes/auth.php';

// Verify admin authentication
requireAuth();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

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
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function saveService() {
    global $conn;
    
    $serviceId = $_POST['service_id'] ?? null;
    $serviceName = trim($_POST['service_name']);
    $category = trim($_POST['category'] ?? '');
    $basePrice = floatval($_POST['base_price']);
    $description = trim($_POST['service_description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $customizable = isset($_POST['customizable']) ? 1 : 0;
    
    // Validate required fields
    if (empty($serviceName) || $basePrice < 0) {
        echo json_encode(['success' => false, 'message' => 'Service name and base price are required']);
        return;
    }
    
    // Build customization options JSON
    $customizationOptions = buildCustomizationOptions();
    
    if ($serviceId) {
        // Update existing service
        $stmt = $conn->prepare("
            UPDATE tbl_services 
            SET service_name = ?, service_description = ?, base_price = ?, 
                category = ?, customizable = ?, customization_options = ?, status = ?
            WHERE service_id = ?
        ");
        $stmt->bind_param("ssdssssi", $serviceName, $description, $basePrice, $category, 
                         $customizable, $customizationOptions, $status, $serviceId);
    } else {
        // Insert new service
        $stmt = $conn->prepare("
            INSERT INTO tbl_services (service_name, service_description, base_price, 
                                    category, customizable, customization_options, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdssss", $serviceName, $description, $basePrice, $category, 
                         $customizable, $customizationOptions, $status);
    }
    
    if ($stmt->execute()) {
        $serviceId = $serviceId ?: $stmt->insert_id;
        $stmt->close();
        
        // Save service details
        saveServiceDetails($serviceId);
        
        // Save service features
        saveServiceFeatures($serviceId);
        
        echo json_encode(['success' => true, 'message' => 'Service saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving service: ' . $stmt->error]);
    }
}

function buildCustomizationOptions() {
    $options = ['options' => []];
    
    foreach ($_POST['customization'] ?? [] as $optionData) {
        if (empty($optionData['name'])) continue;
        
        $optionName = trim($optionData['name']);
        $optionType = $optionData['type'] ?? 'text';
        
        $optionConfig = [
            'type' => $optionType,
            'price' => floatval($optionData['price'] ?? 0),
            'price_type' => $optionData['price_type'] ?? 'fixed'
        ];
        
        // Add type-specific configurations
        if ($optionType === 'number') {
            $optionConfig['min'] = intval($optionData['min'] ?? 1);
            $optionConfig['max'] = intval($optionData['max'] ?? 100);
        } elseif ($optionType === 'select' && !empty($optionData['options'])) {
            $optionConfig['choices'] = array_map('trim', explode(',', $optionData['options']));
        }
        
        $options['options'][$optionName] = $optionConfig;
    }
    
    return empty($options['options']) ? '[]' : json_encode($options);
}

function saveServiceDetails($serviceId) {
    global $conn;
    
    // Delete existing details
    $stmt = $conn->prepare("DELETE FROM tbl_service_details WHERE service_id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $stmt->close();
    
    // Insert new details
    foreach ($_POST['details'] ?? [] as $detailData) {
        $detailName = trim($detailData['detail_name'] ?? '');
        $priceMin = floatval($detailData['price_min'] ?? 0);
        $priceMax = floatval($detailData['price_max'] ?? 0);
        
        if (!empty($detailName)) {
            $stmt = $conn->prepare("
                INSERT INTO tbl_service_details (service_id, detail_name, price_min, price_max)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isdd", $serviceId, $detailName, $priceMin, $priceMax);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function saveServiceFeatures($serviceId) {
    global $conn;
    
    // Delete existing features
    $stmt = $conn->prepare("DELETE FROM tbl_service_features WHERE service_id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $stmt->close();
    
    // Insert new features
    foreach ($_POST['features'] ?? [] as $featureData) {
        $featureName = trim($featureData['feature_name'] ?? '');
        
        if (!empty($featureName)) {
            $stmt = $conn->prepare("
                INSERT INTO tbl_service_features (service_id, feature_name)
                VALUES (?, ?)
            ");
            $stmt->bind_param("is", $serviceId, $featureName);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function loadService() {
    global $conn;
    
    $serviceId = intval($_GET['service_id']);
    
    if ($serviceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
        return;
    }
    
    // Load service basic info
    $stmt = $conn->prepare("
        SELECT * FROM tbl_services WHERE service_id = ?
    ");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $stmt->close();
    
    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        return;
    }
    
    // Load service details
    $stmt = $conn->prepare("
        SELECT * FROM tbl_service_details WHERE service_id = ?
    ");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Load service features
    $stmt = $conn->prepare("
        SELECT * FROM tbl_service_features WHERE service_id = ?
    ");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $features = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Parse customization options
    $service['customization_options'] = json_decode($service['customization_options'] ?? '[]', true) ?? [];
    $service['details'] = $details;
    $service['features'] = $features;
    
    echo json_encode(['success' => true, 'service' => $service]);
}

function deleteService() {
    global $conn;
    
    $serviceId = intval($_GET['service_id']);
    
    if ($serviceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
        return;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete related records first
        $stmt = $conn->prepare("DELETE FROM tbl_service_details WHERE service_id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM tbl_service_features WHERE service_id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $stmt->close();
        
        // Check if service is used in any bookings
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_booking_services WHERE service_id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookingCount = $result->fetch_assoc()['count'];
        $stmt->close();
        
        if ($bookingCount > 0) {
            // Instead of deleting, set to inactive
            $stmt = $conn->prepare("UPDATE tbl_services SET status = 'inactive' WHERE service_id = ?");
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $stmt->close();
            $message = "Service has been deactivated because it's used in existing bookings";
        } else {
            // Delete the service
            $stmt = $conn->prepare("DELETE FROM tbl_services WHERE service_id = ?");
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $stmt->close();
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