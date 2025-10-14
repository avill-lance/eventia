<?php
session_start();
include __DIR__ . '/../includes/db-config.php';
include __DIR__ . '/../functions/function.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    $userId = $_POST['user_id'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $response['message'] = 'First name, last name, and email are required';
        echo json_encode($response);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
        echo json_encode($response);
        exit;
    }

    if (empty($userId)) {
        // Create new user
        if (empty($password)) {
            $response['message'] = 'Password is required for new users';
            echo json_encode($response);
            exit;
        }

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $response['message'] = 'Email already exists';
            $checkStmt->close();
            echo json_encode($response);
            exit;
        }
        $checkStmt->close();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO tbl_users (first_name, last_name, email, phone, city, zip, address, role, status, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssssssss", $firstName, $lastName, $email, $phone, $city, $zip, $address, $role, $status, $hashedPassword);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'User created successfully';
        } else {
            $response['message'] = 'Error creating user: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        // Update existing user
        if (empty($password)) {
            // Update without password
            $stmt = $conn->prepare("UPDATE tbl_users SET first_name = ?, last_name = ?, email = ?, phone = ?, city = ?, zip = ?, address = ?, role = ?, status = ? WHERE user_id = ?");
            $stmt->bind_param("sssssssssi", $firstName, $lastName, $email, $phone, $city, $zip, $address, $role, $status, $userId);
        } else {
            // Update with new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE tbl_users SET first_name = ?, last_name = ?, email = ?, phone = ?, city = ?, zip = ?, address = ?, role = ?, status = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssssssssssi", $firstName, $lastName, $email, $phone, $city, $zip, $address, $role, $status, $hashedPassword, $userId);
        }
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'User updated successfully';
        } else {
            $response['message'] = 'Error updating user: ' . $stmt->error;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>