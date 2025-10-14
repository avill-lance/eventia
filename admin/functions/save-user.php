<?php
session_start();
include __DIR__ . '/../includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit();
}

$userId = $_POST['user_id'] ?? null;
$isNew = empty($userId);

try {
    // Validate required fields
    $required = ['first_name', 'last_name', 'email'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit();
        }
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit();
    }
    
    // Check for duplicate email (excluding current user if editing)
    $emailCheck = "SELECT user_id FROM tbl_users WHERE email = ?";
    if (!$isNew) {
        $emailCheck .= " AND user_id != ?";
    }
    
    $stmt = $conn->prepare($emailCheck);
    if ($isNew) {
        $stmt->bind_param("s", $_POST['email']);
    } else {
        $stmt->bind_param("si", $_POST['email'], $userId);
    }
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered']);
        exit();
    }
    $stmt->close();
    
    if ($isNew) {
        // New user - password is required
        if (empty($_POST['password'])) {
            echo json_encode(['success' => false, 'message' => 'Password is required for new users']);
            exit();
        }
        
        $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';
        $phone = $_POST['phone'] ?? '';
        $city = $_POST['city'] ?? '';
        $zip = $_POST['zip'] ?? '';
        $address = $_POST['address'] ?? '';
        
        $stmt = $conn->prepare("
            INSERT INTO tbl_users (first_name, last_name, email, phone, city, zip, address, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param(
            "ssssssssss",
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $phone,
            $city,
            $zip,
            $address,
            $hashedPassword,
            $role,
            $status
        );
    } else {
        // Update existing user
        $phone = $_POST['phone'] ?? '';
        $city = $_POST['city'] ?? '';
        $zip = $_POST['zip'] ?? '';
        $address = $_POST['address'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';
        
        if (!empty($_POST['password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt = $conn->prepare("
                UPDATE tbl_users 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, city = ?, zip = ?, address = ?, password = ?, role = ?, status = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssssssssssi", $_POST['first_name'], $_POST['last_name'], $_POST['email'], $phone, $city, $zip, $address, $hashedPassword, $role, $status, $userId);
        } else {
            $stmt = $conn->prepare("
                UPDATE tbl_users 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, city = ?, zip = ?, address = ?, role = ?, status = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("sssssssssi", $_POST['first_name'], $_POST['last_name'], $_POST['email'], $phone, $city, $zip, $address, $role, $status, $userId);
        }
    }
    
    if ($stmt->execute()) {
        $message = $isNew ? 'User created successfully' : 'User updated successfully';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>