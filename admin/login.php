<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

include __DIR__ . '/includes/db-config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Debug: Check if we're getting the form data
    error_log("Login attempt - Username: $username");
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Use MySQLi prepared statement for security
        $stmt = $conn->prepare("SELECT id, username, password FROM tbl_admin WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Debug: Check what we found
                error_log("Found admin: " . $admin['username']);
                error_log("Stored hash: " . $admin['password']);
                
                // Verify the password - using password_verify for bcrypt
                if (password_verify($password, $admin['password'])) {
                    // Password is correct, create session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    // Debug: Session created
                    error_log("Login successful for user: " . $admin['username']);
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    // Debug: Password verification failed
                    error_log("Password verification failed for user: $username");
                    $error = 'Invalid username or password';
                }
            } else {
                // Debug: No user found
                error_log("No admin found with username: $username");
                $error = 'Invalid username or password';
            }
            
            $stmt->close();
        } else {
            $error = 'Database error: Unable to prepare statement';
            error_log("Prepare statement failed: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Eventia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; }
        .login-container { max-width: 400px; margin: 100px auto; padding: 20px; }
        .login-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .login-title { text-align: center; margin-bottom: 1.5rem; color: #1e293b; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        .btn { width: 100%; padding: 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .btn:hover { background: #2563eb; }
        .error { color: #dc2626; text-align: center; margin-bottom: 1rem; padding: 0.5rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; }
        .debug-info { color: #6b7280; font-size: 0.875rem; margin-top: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1 class="login-title">Eventia Admin</h1>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <div class="debug-info">
                Default credentials: admin / password
            </div>
        </div>
    </div>
</body>
</html>