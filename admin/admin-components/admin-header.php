<?php
include __DIR__ . '/../includes/auth.php';
requireAuth();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventia Admin</title>
    <link rel="stylesheet" href="admin-css/admin-style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>
<body>
    <div class="layout">
        <nav class="sidebar" id="sidebar">
            <div class="brand">
                <div class="brand-logo"></div>
                <h1>Eventia Admin</h1>
            </div>
            <div style="color: var(--muted); font-size: 0.875rem; margin-bottom: 20px;">
                Logged in as: <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </div>
            
            <div class="nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="packages/index.php" class="nav-item">
                    <i class="fas fa-box"></i> Packages
                </a>
                <a href="services/index.php" class="nav-item">
                    <i class="fas fa-concierge-bell"></i> Services
                </a>
                <a href="blog/index.php" class="nav-item">
                    <i class="fas fa-blog"></i> Blog
                </a>
                <a href="bookings/index.php" class="nav-item">
                    <i class="fas fa-calendar-check"></i> Bookings
                </a>
                <a href="reviews/index.php" class="nav-item">
                    <i class="fas fa-star"></i> Reviews
                </a>
                <a href="users/index.php" class="nav-item">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="logout.php" class="nav-item" style="color: var(--danger);">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
        
        <div class="main-content">
            <div class="topbar">
                <button class="btn menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <main class="main">