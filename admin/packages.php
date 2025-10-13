<?php
include __DIR__ . '/admin-components/admin-header.php';
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Fetch all packages
$packages = [];
try {
    $stmt = $conn->prepare("SELECT * FROM tbl_packages ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $packages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error = "Error loading packages: " . $e->getMessage();
}
?>

<!-- PACKAGES MANAGEMENT -->
<section id="view-packages" class="view">
    <div class="section-header">
        <h2>Packages Management</h2>
        <button class="btn primary" onclick="openPackageModal()">
            <i class="bi bi-plus-circle"></i> Add New Package
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table id="packagesTable" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Package Name</th>
                        <th>Event Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $package): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($package['package_name']); ?></td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($package['event_type']); ?></span>
                        </td>
                        <td>₱<?php echo number_format($package['base_price'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $package['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo ucfirst($package['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($package['created_at'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="editPackage(<?php echo $package['package_id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deletePackage(<?php echo $package['package_id']; ?>, '<?php echo htmlspecialchars($package['package_name']); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- PACKAGE EDITOR MODAL -->
<dialog id="packageModal">
    <div class="modal-header">
        <strong id="packageModalTitle">Add New Package</strong>
        <button class="btn" onclick="closePackageModal()">✕</button>
    </div>
    <div class="modal-body">
        <form id="packageForm" action="packages-process.php" method="POST">
            <input type="hidden" id="packageId" name="package_id">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="field">
                        <label for="packageName">Package Name *</label>
                        <input type="text" id="packageName" name="package_name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label for="eventType">Event Type *</label>
                        <input type="text" id="eventType" name="event_type" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="field">
                        <label for="basePrice">Base Price (₱) *</label>
                        <input type="number" id="basePrice" name="base_price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label for="packageStatus">Status</label>
                        <select id="packageStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="field">
                <label for="packageDescription">Description *</label>
                <textarea id="packageDescription" name="package_description" rows="4" required></textarea>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px;">
                <button class="btn" type="button" onclick="closePackageModal()">Cancel</button>
                <button class="btn primary" type="submit" id="packageSaveBtn">Save Package</button>
            </div>
        </form>
    </div>
</dialog>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#packagesTable').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [5] } // Disable sorting for actions column
        ]
    });
});

// Package Modal Functions
function openPackageModal() {
    const modal = document.getElementById('packageModal');
    const title = document.getElementById('packageModalTitle');
    const form = document.getElementById('packageForm');
    
    title.textContent = 'Add New Package';
    form.reset();
    document.getElementById('packageId').value = '';
    document.getElementById('packageStatus').value = 'active';
    
    modal.showModal();
}

function closePackageModal() {
    document.getElementById('packageModal').close();
}

function editPackage(packageId) {
    // For traditional form submission, we'll redirect to pre-fill the form
    // In a real implementation, you might want to pre-fill via PHP or use a different approach
    document.getElementById('packageId').value = packageId;
    
    // Set modal title for edit
    document.getElementById('packageModalTitle').textContent = 'Edit Package';
    
    // Show the modal - the form will be empty initially
    // In a production environment, you'd want to pre-fill the form data
    // This could be done by fetching the package data and populating the fields
    document.getElementById('packageModal').showModal();
    
    // Note: For a complete implementation, you might want to:
    // 1. Fetch package data via AJAX and populate the form
    // 2. Or use a separate edit page
    // 3. Or pre-fill the form via PHP when the page loads with edit data
}

function deletePackage(packageId, packageName) {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete "${packageName}". This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form and submit it for deletion
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'packages-process.php';
            
            const packageIdInput = document.createElement('input');
            packageIdInput.type = 'hidden';
            packageIdInput.name = 'package_id';
            packageIdInput.value = packageId;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo $_SESSION['csrf_token']; ?>';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            
            form.appendChild(packageIdInput);
            form.appendChild(csrfInput);
            form.appendChild(actionInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include __DIR__ . '/admin-components/admin-footer.php'; ?>