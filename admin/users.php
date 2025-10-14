<?php 
include __DIR__ . '/admin-components/admin-header.php';
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Fetch users data
$users = [];
$userStats = [];
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$roleFilter = $_GET['role'] ?? 'all';

try {
    // Build query with filters
    $query = "SELECT u.*, 
                     COUNT(DISTINCT b.booking_id) as booking_count,
                     COUNT(DISTINCT t.transaction_id) as transaction_count
              FROM tbl_users u
              LEFT JOIN tbl_bookings b ON u.user_id = b.user_id
              LEFT JOIN tbl_transactions t ON u.user_id = t.user_id";
    
    $whereClauses = [];
    $params = [];
    $types = '';
    
    if (!empty($searchTerm)) {
        $whereClauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $types .= 'sss';
    }
    
    if ($statusFilter !== 'all') {
        $whereClauses[] = "u.status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
    
    if ($roleFilter !== 'all') {
        $whereClauses[] = "u.role = ?";
        $params[] = $roleFilter;
        $types .= 's';
    }
    
    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    $query .= " GROUP BY u.user_id ORDER BY u.user_id DESC";
    
    // Prepare and execute query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Fetch user statistics
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_users");
    $userStats['totalUsers'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_users WHERE status = 'active'");
    $userStats['activeUsers'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_users WHERE status = 'inactive'");
    $userStats['inactiveUsers'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_users WHERE role = 'admin'");
    $userStats['adminUsers'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $userStats['newUsersThisWeek'] = $result->fetch_assoc()['total'];
    
} catch (Exception $e) {
    $error = "Error loading users data: " . $e->getMessage();
}

$conn->close();
?>

<!-- USERS MANAGEMENT -->
<section id="view-users" class="view">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; font-size: 24px; font-weight: 700;">Users Management</h2>
        <button class="btn primary" onclick="showUserEditor()" style="display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-plus-circle"></i> Add New User
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert-toast success" style="background: var(--ok); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; border: 1px solid rgba(34, 197, 94, 0.25);">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; margin-left: 12px; cursor: pointer;">✕</button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert-toast danger" style="background: var(--danger); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; border: 1px solid rgba(239, 68, 68, 0.25);">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; margin-left: 12px; cursor: pointer;">✕</button>
        </div>
    <?php endif; ?>

    <!-- User Statistics Cards -->
    <div class="cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 20px; border-left: 4px solid var(--brand);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Total Users</h3>
            <div class="big" style="font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 4px;"><?php echo htmlspecialchars($userStats['totalUsers'] ?? 0); ?></div>
            <div class="muted" style="font-size: 12px; color: var(--muted);">All registered users</div>
        </div>
        <div class="card" style="background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 20px; border-left: 4px solid var(--ok);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Active Users</h3>
            <div class="big" style="font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 4px;"><?php echo htmlspecialchars($userStats['activeUsers'] ?? 0); ?></div>
            <div class="muted" style="font-size: 12px; color: var(--muted);">Currently active</div>
        </div>
        <div class="card" style="background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 20px; border-left: 4px solid var(--warn);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">Admin Users</h3>
            <div class="big" style="font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 4px;"><?php echo htmlspecialchars($userStats['adminUsers'] ?? 0); ?></div>
            <div class="muted" style="font-size: 12px; color: var(--muted);">Administrator accounts</div>
        </div>
        <div class="card" style="background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 20px; border-left: 4px solid var(--info);">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: var(--muted); font-weight: 500;">New This Week</h3>
            <div class="big" style="font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 4px;"><?php echo htmlspecialchars($userStats['newUsersThisWeek'] ?? 0); ?></div>
            <div class="muted" style="font-size: 12px; color: var(--muted);">Recent signups</div>
        </div>
    </div>

    <!-- Advanced Filter Panel -->
    <div class="card filter-panel" style="margin-bottom: 24px; border-left: 4px solid var(--brand);">
        <div class="card-body">
            <h4 style="margin: 0 0 16px 0; color: var(--text); font-size: 16px;">Filter Users</h4>
            <form method="GET" class="form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div class="field">
                    <label>Search Users</label>
                    <input type="text" name="search" placeholder="Search by name or email..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>" style="width: 100%;">
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status" onchange="this.form.submit()" style="width: 100%;">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="field">
                    <label>Role</label>
                    <select name="role" onchange="this.form.submit()" style="width: 100%;">
                        <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                        <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="field" style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn" onclick="resetFilters()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">User</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Contact</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Role</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Status</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Bookings</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Transactions</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Joined</th>
                            <th width="180" style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr data-user-id="<?php echo $user['user_id']; ?>" style="transition: all 0.2s ease;">
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                        <div class="user-info" style="display: flex; align-items: center; gap: 12px;">
                                            <div class="avatar" style="width: 40px; height: 40px; border-radius: 50%; background: var(--brand); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                                                <?php 
                                                $initials = '';
                                                if (!empty($user['first_name'])) {
                                                    $initials .= strtoupper(substr($user['first_name'], 0, 1));
                                                }
                                                if (!empty($user['last_name'])) {
                                                    $initials .= strtoupper(substr($user['last_name'], 0, 1));
                                                }
                                                if (empty($initials)) {
                                                    $initials = 'U';
                                                }
                                                echo $initials;
                                                ?>
                                            </div>
                                            <div>
                                                <strong style="color: var(--text); display: block; margin-bottom: 2px;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                <div style="color: var(--muted); font-size: 12px;">ID: <?php echo htmlspecialchars($user['user_id']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                        <div style="color: var(--text); margin-bottom: 4px;"><?php echo htmlspecialchars($user['email']); ?></div>
                                        <?php if (!empty($user['phone'])): ?>
                                            <div style="color: var(--muted); font-size: 12px;"><?php echo htmlspecialchars($user['phone']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                        <span class="status <?php echo ($user['role'] ?? 'user') === 'admin' ? 'live' : 'draft'; ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; border: 1px solid var(--border); font-size: 12px; font-weight: 500;">
                                            <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                        <span class="status <?php echo $user['status'] === 'active' ? 'live' : 'draft'; ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; border: 1px solid var(--border); font-size: 12px; font-weight: 500;">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">
                                        <strong style="color: var(--text);"><?php echo htmlspecialchars($user['booking_count'] ?? 0); ?></strong>
                                    </td>
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">
                                        <strong style="color: var(--text);"><?php echo htmlspecialchars($user['transaction_count'] ?? 0); ?></strong>
                                    </td>
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                        <div style="color: var(--muted); font-size: 12px;">
                                            <?php 
                                            if (!empty($user['created_at'])) {
                                                echo date('M j, Y', strtotime($user['created_at']));
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                            <button class="btn primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="Edit" style="padding: 6px 10px; font-size: 12px;">
                                                <i class="bi bi-pencil">Edit</i>
                                            </button>
                                            <button class="btn" onclick="viewUserDetails(<?php echo $user['user_id']; ?>)" title="View Details" style="padding: 6px 10px; font-size: 12px;">
                                                <i class="bi bi-eye">View</i>
                                            </button>
                                            <?php if ($user['status'] === 'active'): ?>
                                                <button class="btn warn" onclick="toggleUserStatus(<?php echo $user['user_id']; ?>, 'inactive')" title="Deactivate" style="padding: 6px 10px; font-size: 12px;">
                                                    <i class="bi bi-pause-circle">Deactivate</i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn ok" onclick="toggleUserStatus(<?php echo $user['user_id']; ?>, 'active')" title="Activate" style="padding: 6px 10px; font-size: 12px;">
                                                    <i class="bi bi-play-circle">Activate</i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--muted);">
                                    <div style="font-size: 16px; margin-bottom: 8px;">No users found</div>
                                    <div style="font-size: 14px;">Try adjusting your search filters</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <div style="color: var(--muted); font-size: 14px;">
            Showing <?php echo count($users); ?> user(s)
        </div>
        <div class="pagination-controls" style="display: flex; gap: 8px; align-items: center;">
            <button class="btn" disabled style="padding: 8px 12px;">Previous</button>
            <span style="color: var(--text); font-size: 14px;">Page 1 of 1</span>
            <button class="btn" disabled style="padding: 8px 12px;">Next</button>
        </div>
    </div>
</section>

<!-- MODAL: User Editor -->
<dialog id="userEditorModal">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border);">
        <strong id="userEditorTitle" style="font-size: 18px;">Add New User</strong>
        <button class="btn" onclick="closeUserEditor()" style="padding: 6px 10px;">✕</button>
    </div>
    <div class="modal-body" style="padding: 20px;">
        <form id="userEditorForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" id="userId" name="user_id">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="field">
                    <label for="userFirstName">First Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="userFirstName" name="first_name" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please enter a first name.</div>
                </div>
                <div class="field">
                    <label for="userLastName">Last Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="userLastName" name="last_name" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please enter a last name.</div>
                </div>
                <div class="field">
                    <label for="userEmail">Email <span style="color: var(--danger);">*</span></label>
                    <input type="email" id="userEmail" name="email" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please enter a valid email address.</div>
                </div>
                <div class="field">
                    <label for="userPhone">Phone</label>
                    <input type="tel" id="userPhone" name="phone" style="width: 100%;">
                </div>
                <div class="field">
                    <label for="userRole">Role</label>
                    <select id="userRole" name="role" style="width: 100%;">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="field">
                    <label for="userStatus">Status</label>
                    <select id="userStatus" name="status" style="width: 100%;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="field">
                    <label for="userCity">City</label>
                    <input type="text" id="userCity" name="city" style="width: 100%;">
                </div>
                <div class="field">
                    <label for="userZip">ZIP Code</label>
                    <input type="text" id="userZip" name="zip" style="width: 100%;">
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="userAddress">Address</label>
                    <input type="text" id="userAddress" name="address" style="width: 100%;">
                </div>
                <div class="field" id="passwordField" style="grid-column: 1 / -1;">
                    <label for="userPassword">Password</label>
                    <input type="password" id="userPassword" name="password" placeholder="Leave blank to keep current password" style="width: 100%;">
                    <div style="color: var(--muted); font-size: 12px; margin-top: 4px;">
                        Only enter if you want to change the password
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button class="btn" type="button" onclick="closeUserEditor()">Cancel</button>
                <button class="btn primary" type="submit" id="userSaveBtn" style="display: flex; align-items: center; gap: 6px;">
                    <span id="saveSpinner" style="display: none;">⏳</span>
                    Save User
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- Loading Overlay -->
<div id="globalSpinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: var(--panel); padding: 24px; border-radius: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 12px;">
        <div style="width: 24px; height: 24px; border: 2px solid var(--border); border-top: 2px solid var(--brand); border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <span>Loading...</span>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.table-responsive {
    overflow-x: auto;
}

.table tbody tr:hover {
    background: rgba(79, 70, 229, 0.05);
    transform: translateY(-1px);
}

.status.live {
    background: rgba(34, 197, 94, 0.12);
    border-color: rgba(34, 197, 94, 0.25);
    color: var(--ok);
}

.status.draft {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.25);
    color: var(--warn);
}

/* Responsive design */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start !important;
    }
    
    .form {
        grid-template-columns: 1fr !important;
    }
    
    .table {
        font-size: 14px;
    }
    
    .table td {
        padding: 12px 8px !important;
    }
    
    .cards {
        grid-template-columns: 1fr !important;
    }
}

/* Modal styles */
#userEditorModal {
    border: none;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    max-width: 600px;
    width: 90%;
}

#userEditorModal::backdrop {
    background: rgba(15, 23, 42, 0.7);
}
</style>

<script>
// User management functions
function showUserEditor() {
    document.getElementById('userEditorTitle').textContent = 'Add New User';
    document.getElementById('userEditorForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordField').style.display = 'block';
    document.getElementById('userPassword').required = true;
    document.getElementById('userEditorModal').showModal();
}

function editUser(user) {
    document.getElementById('userEditorTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.user_id;
    document.getElementById('userFirstName').value = user.first_name || '';
    document.getElementById('userLastName').value = user.last_name || '';
    document.getElementById('userEmail').value = user.email || '';
    document.getElementById('userPhone').value = user.phone || '';
    document.getElementById('userCity').value = user.city || '';
    document.getElementById('userZip').value = user.zip || '';
    document.getElementById('userAddress').value = user.address || '';
    document.getElementById('userRole').value = user.role || 'user';
    document.getElementById('userStatus').value = user.status || 'active';
    document.getElementById('passwordField').style.display = 'block';
    document.getElementById('userPassword').required = false;
    document.getElementById('userPassword').placeholder = 'Leave blank to keep current password';
    document.getElementById('userEditorModal').showModal();
}

function closeUserEditor() {
    document.getElementById('userEditorModal').close();
}

function viewUserDetails(userId) {
    showToast('View user details for ID: ' + userId, 'info');
}

function toggleUserStatus(userId, newStatus) {
    if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this user?`)) {
        showLoading();
        fetch('update-user-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                status: newStatus,
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`User status updated to ${newStatus}`, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error updating user status: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error updating user status', 'error');
        })
        .finally(() => {
            hideLoading();
        });
    }
}

function resetFilters() {
    window.location.href = window.location.pathname;
}

// Form submission handler
document.getElementById('userEditorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Basic validation
    const firstName = document.getElementById('userFirstName');
    const lastName = document.getElementById('userLastName');
    const email = document.getElementById('userEmail');
    const isNew = !document.getElementById('userId').value;
    
    let isValid = true;
    
    if (!firstName.value.trim()) {
        showFieldError(firstName, 'First name is required');
        isValid = false;
    } else {
        clearFieldError(firstName);
    }
    
    if (!lastName.value.trim()) {
        showFieldError(lastName, 'Last name is required');
        isValid = false;
    } else {
        clearFieldError(lastName);
    }
    
    if (!email.value.trim() || !isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    } else {
        clearFieldError(email);
    }
    
    if (isNew && !document.getElementById('userPassword').value) {
        showFieldError(document.getElementById('userPassword'), 'Password is required for new users');
        isValid = false;
    } else {
        clearFieldError(document.getElementById('userPassword'));
    }
    
    if (!isValid) {
        showToast('Please fill all required fields correctly', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const saveBtn = document.getElementById('userSaveBtn');
    const spinner = document.getElementById('saveSpinner');
    
    saveBtn.disabled = true;
    spinner.style.display = 'inline';
    
    fetch('save-user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`User ${isNew ? 'created' : 'updated'} successfully`, 'success');
            closeUserEditor();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Error saving user', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error saving user', 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
        spinner.style.display = 'none';
    });
});

function showFieldError(field, message) {
    field.style.borderColor = 'var(--danger)';
    const feedback = field.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
        feedback.style.display = 'block';
    }
}

function clearFieldError(field) {
    field.style.borderColor = 'var(--border)';
    const feedback = field.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.style.display = 'none';
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Utility functions
function showLoading() {
    document.getElementById('globalSpinner').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('globalSpinner').style.display = 'none';
}

function showToast(message, type = 'info') {
    // Remove any existing toasts
    const existingToasts = document.querySelectorAll('.alert-toast:not(.success):not(.danger)');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `alert-toast ${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        padding: 12px 16px;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        border: 1px solid;
        animation: slideIn 0.3s ease;
    `;
    
    const bgColor = type === 'error' ? 'var(--danger)' : 
                   type === 'success' ? 'var(--ok)' : 
                   type === 'warning' ? 'var(--warn)' : 'var(--brand)';
    
    toast.style.background = bgColor;
    toast.style.borderColor = type === 'error' ? 'rgba(239, 68, 68, 0.25)' : 
                             type === 'success' ? 'rgba(34, 197, 94, 0.25)' : 
                             type === 'warning' ? 'rgba(245, 158, 11, 0.25)' : 'rgba(79, 70, 229, 0.25)';
    
    toast.innerHTML = `
        ${message}
        <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; margin-left: 12px; cursor: pointer; float: right;">✕</button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php include __DIR__ . '/admin-components/admin-footer.php'; ?>