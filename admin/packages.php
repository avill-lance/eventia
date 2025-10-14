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
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; font-size: 24px; font-weight: 700;">Packages Management</h2>
        <button class="btn primary" onclick="openPackageModal()" style="display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-plus-circle"></i> 
            <span class="btn-text">Add New Package</span>
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

    <!-- NEW: Horizontal Tabs Filter Panel -->
    <div class="card filter-panel" style="margin-bottom: 24px; border-left: 4px solid var(--brand);">
        <div class="card-body">
            <h4 style="margin: 0 0 16px 0; color: var(--text); font-size: 16px;">Filter Packages</h4>
            
            <!-- Horizontal Tabs Navigation -->
            <div class="filter-tabs" style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <button class="filter-tab active" data-tab="all" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--brand); color: white; font-size: 14px;">
                    All Packages
                </button>
                <button class="filter-tab" data-tab="eventType" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--panel); color: var(--text); font-size: 14px;">
                    By Event Type
                </button>
                <button class="filter-tab" data-tab="status" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--panel); color: var(--text); font-size: 14px;">
                    By Status
                </button>
                <button class="filter-tab" data-tab="price" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--panel); color: var(--text); font-size: 14px;">
                    By Price Range
                </button>
                <button class="filter-tab" data-tab="reset" onclick="resetFilters()" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--warn); color: white; font-size: 14px;">
                    Reset Filters
                </button>
            </div>

            <!-- Tab Content Areas -->
            <div class="tab-content">
                <!-- Event Type Filter -->
                <div id="eventTypeTab" class="tab-pane" style="display: none; padding: 16px; background: var(--panel-2); border-radius: 8px; margin-bottom: 12px;">
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <label style="font-weight: 600; color: var(--text); min-width: 100px;">Event Type:</label>
                        <select id="filterEventType" style="flex: 1; min-width: 200px; max-width: 300px;">
                            <option value="">All Event Types</option>
                            <?php
                            $eventTypes = array_unique(array_column($packages, 'event_type'));
                            foreach ($eventTypes as $type):
                            ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn primary" onclick="applyEventTypeFilter()" style="display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-filter"></i>
                            <span class="btn-text">Apply Filter</span>
                        </button>
                    </div>
                </div>

                <!-- Status Filter -->
                <div id="statusTab" class="tab-pane" style="display: none; padding: 16px; background: var(--panel-2); border-radius: 8px; margin-bottom: 12px;">
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <label style="font-weight: 600; color: var(--text); min-width: 80px;">Status:</label>
                        <select id="filterStatus" style="flex: 1; min-width: 180px; max-width: 250px;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button class="btn primary" onclick="applyStatusFilter()" style="display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-filter"></i>
                            <span class="btn-text">Apply Filter</span>
                        </button>
                    </div>
                </div>

                <!-- Price Range Filter -->
                <div id="priceTab" class="tab-pane" style="display: none; padding: 16px; background: var(--panel-2); border-radius: 8px; margin-bottom: 12px;">
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <label style="font-weight: 600; color: var(--text); min-width: 100px;">Price Range:</label>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <input type="number" id="minPrice" placeholder="Min Price" min="0" style="width: 120px;">
                            <span style="color: var(--muted);">to</span>
                            <input type="number" id="maxPrice" placeholder="Max Price" min="0" style="width: 120px;">
                        </div>
                        <button class="btn primary" onclick="applyPriceFilter()" style="display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-filter"></i>
                            <span class="btn-text">Apply Filter</span>
                        </button>
                    </div>
                </div>

                <!-- Search Filter (Always visible) -->
                <div style="padding: 12px 0;">
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <label style="font-weight: 600; color: var(--text); min-width: 120px;">Search Package:</label>
                        <input type="text" id="searchName" placeholder="Search by package name..." style="flex: 1; min-width: 250px; max-width: 400px;">
                        <button class="btn" onclick="applySearchFilter()" style="display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-search"></i>
                            <span class="btn-text">Search</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions-panel" id="bulkActionsPanel" style="display: none; margin-bottom: 16px; background: var(--warn); border-left: 4px solid var(--warn); border-radius: 12px; padding: 12px 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <span id="selectedCount" style="font-weight: 600; color: white;">0 packages selected</span>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button class="btn ok" onclick="bulkUpdateStatus('active')" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-check-circle"></i>
                    <span class="btn-text">Activate</span>
                </button>
                <button class="btn warn" onclick="bulkUpdateStatus('inactive')" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-pause-circle"></i>
                    <span class="btn-text">Deactivate</span>
                </button>
                <button class="btn danger" onclick="bulkDelete()" style="display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="bi bi-trash"></i>
                    <span class="btn-text">Delete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Packages Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table id="packagesTable" class="table" style="width: 100%; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th width="40" style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" style="cursor: pointer;">
                            </th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Package Name</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Event Type</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Price</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Status</th>
                            <th style="padding: 16px 12px; border-bottom: 1px solid var(--border);">Created</th>
                            <th width="220" style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $package): ?>
                        <tr data-package-id="<?php echo $package['package_id']; ?>" style="transition: all 0.2s ease;">
                            <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <input type="checkbox" class="package-checkbox" value="<?php echo $package['package_id']; ?>" onchange="updateBulkActions()" style="cursor: pointer;">
                            </td>
                            <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <div style="font-weight: 600; color: var(--text); margin-bottom: 4px;"><?php echo htmlspecialchars($package['package_name']); ?></div>
                                <div style="color: var(--muted); font-size: 12px; line-height: 1.4;">
                                    <?php echo htmlspecialchars(substr($package['package_description'], 0, 60)); ?>...
                                </div>
                            </td>
                            <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <span class="status" style="background: rgba(79, 70, 229, 0.12); border-color: rgba(79, 70, 229, 0.25);">
                                    <?php echo htmlspecialchars($package['event_type']); ?>
                                </span>
                            </td>
                            <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <strong style="color: var(--ok);">₱<?php echo number_format($package['base_price'], 2); ?></strong>
                                <span style="display: none;" class="price-value"><?php echo $package['base_price']; ?></span>
                            </td>
                            <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <span class="status <?php echo $package['status'] == 'active' ? 'live' : 'draft'; ?>">
                                    <?php echo ucfirst($package['status']); ?>
                                </span>
                                <span style="display: none;" class="status-value"><?php echo $package['status']; ?></span>
                            </td>
                            <td style="padding: 16px 12px; border-bottom: 1px solid var(--border);">
                                <div style="color: var(--muted); font-size: 12px;"><?php echo date('M j, Y', strtotime($package['created_at'])); ?></div>
                            </td>
                            <td style="padding: 16px 12px; border-bottom: 1px solid var(--border); text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                    <button class="btn primary" onclick="editPackage(<?php echo $package['package_id']; ?>)" title="Edit" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-pencil"></i>
                                        <span class="btn-text">Edit</span>
                                    </button>
                                    <button class="btn danger" onclick="deletePackage(<?php echo $package['package_id']; ?>, '<?php echo htmlspecialchars(addslashes($package['package_name'])); ?>')" title="Delete" style="padding: 6px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-trash"></i>
                                        <span class="btn-text">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- PACKAGE EDITOR MODAL -->
<dialog id="packageModal">
    <div class="modal-header">
        <strong id="packageModalTitle" style="font-size: 18px;">Add New Package</strong>
        <button class="btn" onclick="closePackageModal()" style="padding: 6px 10px;">✕</button>
    </div>
    <div class="modal-body">
        <form id="packageForm" novalidate>
            <input type="hidden" id="packageId" name="package_id">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="save">
            
            <div class="form">
                <div class="field">
                    <label for="packageName">Package Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="packageName" name="package_name" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please enter a package name.</div>
                </div>
                <div class="field">
                    <label for="eventType">Event Type <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="eventType" name="event_type" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please enter an event type.</div>
                </div>
                <div class="field">
                    <label for="basePrice">Base Price (₱) <span style="color: var(--danger);">*</span></label>
                    <input type="number" id="basePrice" name="base_price" step="0.01" min="0" required style="width: 100%;">
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please enter a valid price greater than 0.</div>
                </div>
                <div class="field">
                    <label for="packageStatus">Status</label>
                    <select id="packageStatus" name="status" style="width: 100%;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="packageDescription">Description <span style="color: var(--danger);">*</span></label>
                    <textarea id="packageDescription" name="package_description" rows="4" required style="width: 100%; resize: vertical;"></textarea>
                    <div class="invalid-feedback" style="color: var(--danger); font-size: 12px; display: none;">Please enter a package description.</div>
                </div>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="button" class="btn" onclick="closePackageModal()">Cancel</button>
                <button type="submit" class="btn primary" id="packageSaveBtn" style="display: flex; align-items: center; gap: 6px;">
                    <span id="saveSpinner" style="display: none;">⏳</span>
                    Save Package
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- PACKAGE DETAILS MODAL -->
<dialog id="packageDetailsModal">
    <div class="modal-header">
        <strong style="font-size: 18px;">Package Details</strong>
        <button class="btn" onclick="closePackageDetailsModal()" style="padding: 6px 10px;">✕</button>
    </div>
    <div class="modal-body" id="packageDetailsContent" style="max-height: 60vh; overflow-y: auto;">
        <!-- Details will be loaded via AJAX -->
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

#packagesTable tbody tr:hover {
    background: rgba(79, 70, 229, 0.05);
    transform: translateY(-1px);
}

.status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid var(--border);
    font-size: 12px;
    font-weight: 500;
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

.filter-tab.active {
    background: var(--brand) !important;
    color: white !important;
}

.tab-pane {
    transition: all 0.3s ease;
}

/* Responsive design for buttons */
@media (max-width: 768px) {
    .btn-text {
        display: none;
    }
    
    .section-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start !important;
    }
    
    .filter-tabs {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tab {
        text-align: center;
        width: 100%;
    }
    
    #packagesTable {
        font-size: 14px;
    }
    
    #packagesTable td {
        padding: 12px 8px !important;
    }
    
    .bulk-actions-panel > div {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start !important;
    }
    
    .tab-pane > div {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .tab-pane input,
    .tab-pane select {
        width: 100% !important;
        max-width: none !important;
    }
}

/* Custom scrollbar for modal */
#packageDetailsContent::-webkit-scrollbar {
    width: 6px;
}

#packageDetailsContent::-webkit-scrollbar-track {
    background: var(--panel-2);
    border-radius: 3px;
}

#packageDetailsContent::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 3px;
}

#packageDetailsContent::-webkit-scrollbar-thumb:hover {
    background: var(--muted);
}
</style>

<script>
// Package Modal Functions
function openPackageModal() {
    const modal = document.getElementById('packageModal');
    const title = document.getElementById('packageModalTitle');
    const form = document.getElementById('packageForm');
    
    title.textContent = 'Add New Package';
    form.reset();
    form.classList.remove('was-validated');
    document.getElementById('packageId').value = '';
    document.getElementById('packageStatus').value = 'active';
    
    modal.showModal();
}

function closePackageModal() {
    document.getElementById('packageModal').close();
}

function closePackageDetailsModal() {
    document.getElementById('packageDetailsModal').close();
}

// NEW: Tab Navigation Functions
function setupFilterTabs() {
    const tabs = document.querySelectorAll('.filter-tab');
    tabs.forEach(tab => {
        if (tab.dataset.tab !== 'reset') {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab panes
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.style.display = 'none';
                });
                
                // Show selected tab pane
                const tabId = this.dataset.tab + 'Tab';
                const tabPane = document.getElementById(tabId);
                if (tabPane) {
                    tabPane.style.display = 'block';
                }
            });
        }
    });
}

// NEW: Filter Application Functions
function applyEventTypeFilter() {
    const eventType = document.getElementById('filterEventType').value;
    filterTable();
    showToast(`Filtered by event type: ${eventType || 'All'}`, 'info');
}

function applyStatusFilter() {
    const status = document.getElementById('filterStatus').value;
    filterTable();
    showToast(`Filtered by status: ${status || 'All'}`, 'info');
}

function applyPriceFilter() {
    filterTable();
    showToast('Price filter applied', 'info');
}

function applySearchFilter() {
    filterTable();
    const searchTerm = document.getElementById('searchName').value;
    if (searchTerm) {
        showToast(`Search results for: ${searchTerm}`, 'info');
    }
}

// AJAX function to fetch package data for editing
async function editPackage(packageId) {
    showLoading();
    try {
        const response = await fetch(`packages-ajax.php?action=get_package&package_id=${packageId}`);
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response from server. Expected JSON.');
        }
        
        const packageData = await response.json();
        
        if (packageData.success) {
            document.getElementById('packageId').value = packageData.data.package_id;
            document.getElementById('packageName').value = packageData.data.package_name;
            document.getElementById('eventType').value = packageData.data.event_type;
            document.getElementById('basePrice').value = packageData.data.base_price;
            document.getElementById('packageDescription').value = packageData.data.package_description;
            document.getElementById('packageStatus').value = packageData.data.status;
            
            document.getElementById('packageModalTitle').textContent = 'Edit Package';
            document.getElementById('packageModal').showModal();
        } else {
            showToast('Error loading package data: ' + (packageData.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        console.error('Edit package error:', error);
    } finally {
        hideLoading();
    }
}

// View package details
async function viewPackageDetails(packageId) {
    showLoading();
    try {
        const response = await fetch(`packages-ajax.php?action=get_package_details&package_id=${packageId}`);
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response from server. Expected JSON.');
        }
        
        const packageData = await response.json();
        
        if (packageData.success) {
            document.getElementById('packageDetailsContent').innerHTML = packageData.html;
            document.getElementById('packageDetailsModal').showModal();
        } else {
            showToast('Error loading package details: ' + (packageData.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        console.error('View package details error:', error);
    } finally {
        hideLoading();
    }
}

// Bulk Actions Functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.package-checkbox:not([style*="display: none"])');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const selected = document.querySelectorAll('.package-checkbox:checked');
    const bulkPanel = document.getElementById('bulkActionsPanel');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = `${selected.length} package(s) selected`;
    bulkPanel.style.display = selected.length > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const totalVisibleCheckboxes = document.querySelectorAll('.package-checkbox:not([style*="display: none"])').length;
    const selectAll = document.getElementById('selectAll');
    selectAll.checked = selected.length === totalVisibleCheckboxes && totalVisibleCheckboxes > 0;
    selectAll.indeterminate = selected.length > 0 && selected.length < totalVisibleCheckboxes;
}

async function bulkUpdateStatus(status) {
    const selected = Array.from(document.querySelectorAll('.package-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        showToast('Please select at least one package', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to ${status === 'active' ? 'activate' : 'deactivate'} ${selected.length} package(s)?`)) {
        return;
    }
    
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'bulk_update_status');
        formData.append('package_ids', JSON.stringify(selected));
        formData.append('status', status);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('packages-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response from server. Expected JSON.');
        }
        
        const result = await response.json();
        if (result.success) {
            showToast(`Successfully ${status === 'active' ? 'activated' : 'deactivated'} ${selected.length} package(s)`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error updating packages: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        console.error('Bulk update error:', error);
    } finally {
        hideLoading();
    }
}

function bulkDelete() {
    const selected = Array.from(document.querySelectorAll('.package-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        showToast('Please select at least one package', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selected.length} package(s)? This action cannot be undone.`)) {
        performBulkDelete(selected);
    }
}

async function performBulkDelete(packageIds) {
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'bulk_delete');
        formData.append('package_ids', JSON.stringify(packageIds));
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('packages-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response from server. Expected JSON.');
        }
        
        const result = await response.json();
        if (result.success) {
            showToast(`Successfully deleted ${packageIds.length} package(s)`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error deleting packages: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        console.error('Bulk delete error:', error);
    } finally {
        hideLoading();
    }
}

// Delete single package
function deletePackage(packageId, packageName) {
    if (confirm(`Are you sure you want to delete "${packageName}"? This action cannot be undone.`)) {
        performSingleDelete(packageId);
    }
}

async function performSingleDelete(packageId) {
    showLoading();
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('package_id', packageId);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const response = await fetch('packages-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response from server. Expected JSON.');
        }
        
        const result = await response.json();
        if (result.success) {
            showToast('Package deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error deleting package: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        console.error('Single delete error:', error);
    } finally {
        hideLoading();
    }
}

// Form validation and submission
document.getElementById('packageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Basic validation
    const name = document.getElementById('packageName');
    const eventType = document.getElementById('eventType');
    const price = document.getElementById('basePrice');
    const description = document.getElementById('packageDescription');
    
    let isValid = true;
    
    if (!name.value.trim()) {
        showFieldError(name, 'Package name is required');
        isValid = false;
    } else {
        clearFieldError(name);
    }
    
    if (!eventType.value.trim()) {
        showFieldError(eventType, 'Event type is required');
        isValid = false;
    } else {
        clearFieldError(eventType);
    }
    
    if (!price.value || parseFloat(price.value) <= 0) {
        showFieldError(price, 'Price must be greater than 0');
        isValid = false;
    } else {
        clearFieldError(price);
    }
    
    if (!description.value.trim()) {
        showFieldError(description, 'Description is required');
        isValid = false;
    } else {
        clearFieldError(description);
    }
    
    if (!isValid) {
        showToast('Please fill all required fields correctly', 'error');
        return;
    }
    
    const saveBtn = document.getElementById('packageSaveBtn');
    const spinner = document.getElementById('saveSpinner');
    const formData = new FormData(this);
    
    saveBtn.disabled = true;
    spinner.style.display = 'inline';
    
    try {
        const response = await fetch('packages-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response from server. Expected JSON.');
        }
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            closePackageModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        console.error('Form submission error:', error);
    } finally {
        saveBtn.disabled = false;
        spinner.style.display = 'none';
    }
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

// Utility functions
function resetFilters() {
    console.log('Resetting filters...');
    
    document.getElementById('searchName').value = '';
    document.getElementById('filterEventType').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    
    // Reset tab states
    document.querySelectorAll('.filter-tab').forEach(tab => {
        if (tab.dataset.tab === 'all') {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });
    
    // Hide all tab panes
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.style.display = 'none';
    });
    
    // Reset table filtering
    const rows = document.querySelectorAll('#packagesTable tbody tr');
    rows.forEach(row => row.style.display = '');
    
    // Reset bulk actions
    updateBulkActions();
    
    showToast('Filters reset successfully', 'info');
}

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

// Table filtering function
function filterTable() {
    const nameFilter = document.getElementById('searchName').value.toLowerCase();
    const eventTypeFilter = document.getElementById('filterEventType').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const minPrice = parseFloat(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseFloat(document.getElementById('maxPrice').value) || Infinity;
    
    console.log('Filtering with:', {
        nameFilter,
        eventTypeFilter,
        statusFilter,
        minPrice,
        maxPrice
    });
    
    const rows = document.querySelectorAll('#packagesTable tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const name = row.cells[1].querySelector('div:first-child').textContent.toLowerCase();
        const eventType = row.cells[2].textContent.trim();
        const statusElement = row.cells[4].querySelector('.status-value');
        const status = statusElement ? statusElement.textContent.toLowerCase() : row.cells[4].textContent.toLowerCase();
        const priceElement = row.cells[3].querySelector('.price-value');
        const price = priceElement ? parseFloat(priceElement.textContent) : parseFloat(row.cells[3].textContent.replace('₱', '').replace(/,/g, ''));
        
        const nameMatch = name.includes(nameFilter);
        const eventTypeMatch = !eventTypeFilter || eventType === eventTypeFilter;
        const statusMatch = !statusFilter || status === statusFilter.toLowerCase();
        const priceMatch = price >= minPrice && price <= maxPrice;
        
        const shouldShow = nameMatch && eventTypeMatch && statusMatch && priceMatch;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) visibleCount++;
    });
    
    console.log(`Filter complete: ${visibleCount} of ${rows.length} rows visible`);
    
    // Update bulk actions after filtering
    updateBulkActions();
}

// Initialize when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded, initializing filters...');
    setupFilterTabs();
    
    // Set up filter input events
    document.getElementById('searchName').addEventListener('input', filterTable);
    document.getElementById('filterEventType').addEventListener('change', filterTable);
    document.getElementById('filterStatus').addEventListener('change', filterTable);
    document.getElementById('minPrice').addEventListener('input', filterTable);
    document.getElementById('maxPrice').addEventListener('input', filterTable);
    
    // Apply initial filter state if any filters have values
    const hasActiveFilters = document.getElementById('searchName').value || 
                           document.getElementById('filterEventType').value ||
                           document.getElementById('filterStatus').value ||
                           document.getElementById('minPrice').value ||
                           document.getElementById('maxPrice').value;
    
    if (hasActiveFilters) {
        console.log('Active filters detected, applying initial filter...');
        filterTable();
    }
});

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