<?php 
include __DIR__ . '/admin-components/admin-header.php';
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Fetch all services
$services = [];
try {
    $stmt = $conn->prepare("
        SELECT s.*, 
               COUNT(sd.detail_id) as detail_count,
               COUNT(sf.feature_id) as feature_count
        FROM tbl_services s
        LEFT JOIN tbl_service_details sd ON s.service_id = sd.service_id
        LEFT JOIN tbl_service_features sf ON s.service_id = sf.service_id
        GROUP BY s.service_id
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $services = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error = "Error loading services: " . $e->getMessage();
}
?>

<!-- SERVICES MANAGEMENT -->
<section id="view-services" class="view">
    <div class="page-header">
        <h1>Services Management</h1>
        <button class="btn primary" onclick="openServiceModal()">
            <i class="fas fa-plus"></i> Add New Service
        </button>
    </div>

    <div class="card">
        <table id="servicesTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Name</th>
                    <th>Category</th>
                    <th>Base Price</th>
                    <th>Customizable</th>
                    <th>Details</th>
                    <th>Features</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($service['service_id']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($service['service_name']); ?></strong>
                            <?php if ($service['service_description']): ?>
                                <div class="muted" style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars(substr($service['service_description'], 0, 100)); ?>
                                    <?php echo strlen($service['service_description']) > 100 ? '...' : ''; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($service['category'] ?: 'Uncategorized'); ?></td>
                        <td>₱<?php echo number_format($service['base_price'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $service['customizable'] ? 'success' : 'secondary'; ?>">
                                <?php echo $service['customizable'] ? 'Yes' : 'No'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge info"><?php echo $service['detail_count']; ?> details</span>
                        </td>
                        <td>
                            <span class="badge info"><?php echo $service['feature_count']; ?> features</span>
                        </td>
                        <td>
                            <span class="badge <?php echo $service['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($service['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($service['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn small" onclick="editService(<?php echo $service['service_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn small danger" onclick="deleteService(<?php echo $service['service_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- SERVICE EDITOR MODAL -->
<dialog id="serviceModal">
    <div class="modal-header">
        <strong id="serviceModalTitle">Add New Service</strong>
        <button class="btn" onclick="closeServiceModal()">✕</button>
    </div>
    <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <!-- Tab Navigation -->
        <div class="tabs">
            <button type="button" class="tab-btn active" data-tab="basic">Basic Info</button>
            <button type="button" class="tab-btn" data-tab="customization">Customization</button>
            <button type="button" class="tab-btn" data-tab="details">Price Details</button>
            <button type="button" class="tab-btn" data-tab="features">Features</button>
        </div>

        <form id="serviceForm" enctype="multipart/form-data">
            <input type="hidden" id="service_id" name="service_id">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- Basic Info Tab -->
            <div id="tab-basic" class="tab-content active">
                <div class="grid-2">
                    <div class="field">
                        <label for="service_name">Service Name *</label>
                        <input type="text" id="service_name" name="service_name" required>
                    </div>
                    <div class="field">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" placeholder="e.g., Catering, Decorations">
                    </div>
                    <div class="field">
                        <label for="base_price">Base Price (₱) *</label>
                        <input type="number" id="base_price" name="base_price" step="0.01" min="0" required>
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="field" style="grid-column: 1 / -1;">
                        <label for="service_description">Description</label>
                        <textarea id="service_description" name="service_description" rows="4"></textarea>
                    </div>
                    <div class="field">
                        <label>
                            <input type="checkbox" id="customizable" name="customizable" value="1">
                            Customizable Service
                        </label>
                    </div>
                </div>
            </div>

            <!-- Customization Options Tab -->
            <div id="tab-customization" class="tab-content">
                <div class="field">
                    <label>Customization Options</label>
                    <div class="muted" style="margin-bottom: 16px;">
                        Define options that customers can customize for this service.
                    </div>
                    
                    <div id="customizationOptionsContainer">
                        <!-- Dynamic options will be added here -->
                    </div>
                    
                    <button type="button" class="btn secondary" onclick="addCustomizationOption()" style="margin-top: 12px;">
                        <i class="fas fa-plus"></i> Add Option
                    </button>
                </div>
            </div>

            <!-- Price Details Tab -->
            <div id="tab-details" class="tab-content">
                <div class="field">
                    <label>Service Details & Price Ranges</label>
                    <div class="muted" style="margin-bottom: 16px;">
                        Add different variations or packages for this service.
                    </div>
                    
                    <div id="serviceDetailsContainer">
                        <!-- Dynamic details will be added here -->
                    </div>
                    
                    <button type="button" class="btn secondary" onclick="addServiceDetail()" style="margin-top: 12px;">
                        <i class="fas fa-plus"></i> Add Detail
                    </button>
                </div>
            </div>

            <!-- Features Tab -->
            <div id="tab-features" class="tab-content">
                <div class="field">
                    <label>Service Features</label>
                    <div class="muted" style="margin-bottom: 16px;">
                        List the key features included with this service.
                    </div>
                    
                    <div id="serviceFeaturesContainer">
                        <!-- Dynamic features will be added here -->
                    </div>
                    
                    <button type="button" class="btn secondary" onclick="addServiceFeature()" style="margin-top: 12px;">
                        <i class="fas fa-plus"></i> Add Feature
                    </button>
                </div>
            </div>

            <!-- Form Actions -->
            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 24px; border-top: 1px solid var(--border); padding-top: 16px;">
                <button class="btn" type="button" onclick="closeServiceModal()">Cancel</button>
                <button class="btn primary" type="submit" id="saveServiceBtn">
                    <span id="saveServiceText">Save Service</span>
                    <span id="saveServiceLoading" style="display: none;">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- CONFIRMATION MODAL -->
<dialog id="confirmModal">
    <div class="modal-header">
        <strong>Confirm Action</strong>
        <button class="btn" onclick="closeConfirmModal()">✕</button>
    </div>
    <div class="modal-body">
        <p id="confirmMessage">Are you sure you want to delete this service?</p>
        <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
            <button class="btn" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn danger" id="confirmActionBtn">Delete</button>
        </div>
    </div>
</dialog>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#servicesTable').DataTable({
        "pageLength": 25,
        "order": [[0, 'desc']]
    });
});

// Tab functionality
document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
        const tabId = button.getAttribute('data-tab');
        
        // Update active tab button
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        // Show active tab content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(`tab-${tabId}`).classList.add('active');
    });
});

// Service Modal Functions
function openServiceModal(serviceId = null) {
    if (serviceId) {
        document.getElementById('serviceModalTitle').textContent = 'Edit Service';
        loadServiceData(serviceId);
    } else {
        document.getElementById('serviceModalTitle').textContent = 'Add New Service';
        resetServiceForm();
    }
    document.getElementById('serviceModal').showModal();
}

function closeServiceModal() {
    document.getElementById('serviceModal').close();
}

function resetServiceForm() {
    document.getElementById('serviceForm').reset();
    document.getElementById('service_id').value = '';
    document.getElementById('customizable').checked = false;
    
    // Reset dynamic fields
    document.getElementById('customizationOptionsContainer').innerHTML = '';
    document.getElementById('serviceDetailsContainer').innerHTML = '';
    document.getElementById('serviceFeaturesContainer').innerHTML = '';
    
    // Reset to first tab
    document.querySelectorAll('.tab-btn')[0].click();
}

// Dynamic Form Builders
function addCustomizationOption(optionData = {}) {
    const container = document.getElementById('customizationOptionsContainer');
    const optionId = 'option_' + Date.now();
    
    const optionHtml = `
        <div class="customization-option" style="border: 1px solid var(--border); padding: 16px; margin-bottom: 12px; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <strong>Customization Option</strong>
                <button type="button" class="btn small danger" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label>Option Name *</label>
                    <input type="text" name="customization[${optionId}][name]" value="${optionData.name || ''}" placeholder="e.g., guests, premium_menu" required>
                </div>
                <div class="field">
                    <label>Type *</label>
                    <select name="customization[${optionId}][type]" onchange="toggleOptionFields(this)" required>
                        <option value="number" ${(optionData.type || 'number') === 'number' ? 'selected' : ''}>Number</option>
                        <option value="boolean" ${(optionData.type || 'number') === 'boolean' ? 'selected' : ''}>Yes/No Toggle</option>
                        <option value="select" ${(optionData.type || 'number') === 'select' ? 'selected' : ''}>Dropdown</option>
                    </select>
                </div>
                <div class="field">
                    <label>Price Effect *</label>
                    <select name="customization[${optionId}][price_type]" required>
                        <option value="fixed" ${(optionData.price_type || 'fixed') === 'fixed' ? 'selected' : ''}>Fixed Price</option>
                        <option value="per_unit" ${(optionData.price_type || 'fixed') === 'per_unit' ? 'selected' : ''}>Price Per Unit</option>
                    </select>
                </div>
            </div>
            <div class="option-fields" id="fields_${optionId}">
                <!-- Dynamic fields based on type will be added here -->
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', optionHtml);
    
    // Initialize the dynamic fields
    const select = container.querySelector(`select[name="customization[${optionId}][type]"]`);
    toggleOptionFields(select, optionData);
}

function toggleOptionFields(select, optionData = {}) {
    const optionId = select.name.match(/customization\[(.*?)\]/)[1];
    const fieldsContainer = document.getElementById(`fields_${optionId}`);
    const type = select.value;
    
    let fieldsHtml = '';
    
    if (type === 'number') {
        fieldsHtml = `
            <div class="grid-2">
                <div class="field">
                    <label>Minimum</label>
                    <input type="number" name="customization[${optionId}][min]" value="${optionData.min || 1}">
                </div>
                <div class="field">
                    <label>Maximum</label>
                    <input type="number" name="customization[${optionId}][max]" value="${optionData.max || 100}">
                </div>
            </div>
        `;
    } else if (type === 'select') {
        const optionsValue = Array.isArray(optionData.choices) ? 
            optionData.choices.join(', ') : 
            (optionData.options || optionData.choices || '');
        fieldsHtml = `
            <div class="field">
                <label>Options (comma-separated) *</label>
                <input type="text" name="customization[${optionId}][options]" value="${optionsValue}" placeholder="e.g., Standard,Premium,Deluxe" required>
            </div>
        `;
    }
    // Boolean type doesn't need additional fields
    
    // Always add price field
    fieldsHtml += `
        <div class="field">
            <label>Price (₱) *</label>
            <input type="number" name="customization[${optionId}][price]" step="0.01" min="0" value="${optionData.price || 0}" required>
        </div>
    `;
    
    fieldsContainer.innerHTML = fieldsHtml;
}

function addServiceDetail(detailData = {}) {
    const container = document.getElementById('serviceDetailsContainer');
    const detailId = 'detail_' + Date.now();
    
    const detailHtml = `
        <div class="service-detail" style="border: 1px solid var(--border); padding: 16px; margin-bottom: 12px; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <strong>Service Detail</strong>
                <button type="button" class="btn small danger" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label>Detail Name *</label>
                    <input type="text" name="details[${detailId}][detail_name]" value="${detailData.detail_name || ''}" required>
                </div>
                <div class="field">
                    <label>Minimum Price (₱)</label>
                    <input type="number" name="details[${detailId}][price_min]" step="0.01" min="0" value="${detailData.price_min || 0}">
                </div>
                <div class="field">
                    <label>Maximum Price (₱)</label>
                    <input type="number" name="details[${detailId}][price_max]" step="0.01" min="0" value="${detailData.price_max || 0}">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', detailHtml);
}

function addServiceFeature(featureData = {}) {
    const container = document.getElementById('serviceFeaturesContainer');
    const featureId = 'feature_' + Date.now();
    
    const featureHtml = `
        <div class="service-feature" style="border: 1px solid var(--border); padding: 16px; margin-bottom: 12px; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <strong>Service Feature</strong>
                <button type="button" class="btn small danger" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="field">
                <label>Feature Name *</label>
                <input type="text" name="features[${featureId}][feature_name]" value="${featureData.feature_name || ''}" required>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', featureHtml);
}

// Form Submission
document.getElementById('serviceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    saveService();
});

function saveService() {
    const form = document.getElementById('serviceForm');
    const formData = new FormData(form);
    const saveBtn = document.getElementById('saveServiceBtn');
    const saveText = document.getElementById('saveServiceText');
    const saveLoading = document.getElementById('saveServiceLoading');
    
    // Validate form
    const serviceName = document.getElementById('service_name').value.trim();
    const basePrice = document.getElementById('base_price').value;
    
    if (!serviceName) {
        showToast('Service name is required', 'error');
        return;
    }
    
    if (!basePrice || parseFloat(basePrice) < 0) {
        showToast('Valid base price is required', 'error');
        return;
    }
    
    // Show loading state
    saveBtn.disabled = true;
    saveText.style.display = 'none';
    saveLoading.style.display = 'inline';
    
    fetch('services-ajax.php?action=save', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeServiceModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error saving service: ' + error, 'error');
    })
    .finally(() => {
        // Reset button state
        saveBtn.disabled = false;
        saveText.style.display = 'inline';
        saveLoading.style.display = 'none';
    });
}

function editService(serviceId) {
    openServiceModal(serviceId);
}

function loadServiceData(serviceId) {
    console.log('Loading service data for ID:', serviceId);
    
    // Show loading state
    const saveBtn = document.getElementById('saveServiceBtn');
    saveBtn.disabled = true;
    
    fetch(`services-ajax.php?action=load&service_id=${serviceId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned non-JSON response. Check for PHP errors.');
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success && data.service) {
                console.log('Service data loaded successfully:', data.service);
                populateServiceForm(data.service);
            } else {
                throw new Error(data.message || 'Failed to load service data');
            }
        })
        .catch(error => {
            console.error('Error loading service:', error);
            showToast('Error loading service: ' + error.message, 'error');
            closeServiceModal();
        })
        .finally(() => {
            saveBtn.disabled = false;
        });
}

function populateServiceForm(service) {
    console.log('Populating form with service data:', service);
    
    // Populate basic fields
    document.getElementById('service_id').value = service.service_id || '';
    document.getElementById('service_name').value = service.service_name || '';
    document.getElementById('category').value = service.category || '';
    document.getElementById('base_price').value = service.base_price || 0;
    document.getElementById('service_description').value = service.service_description || '';
    document.getElementById('status').value = service.status || 'active';
    document.getElementById('customizable').checked = (service.customizable == 1 || service.customizable === true);
    
    // Clear existing dynamic content
    document.getElementById('customizationOptionsContainer').innerHTML = '';
    document.getElementById('serviceDetailsContainer').innerHTML = '';
    document.getElementById('serviceFeaturesContainer').innerHTML = '';
    
    // Populate customization options
    if (service.customization_options && service.customization_options.options) {
        console.log('Loading customization options:', service.customization_options.options);
        Object.entries(service.customization_options.options).forEach(([name, config]) => {
            console.log('Adding option:', name, config);
            addCustomizationOption({
                name: name,
                type: config.type || 'number',
                price: config.price || 0,
                price_type: config.price_type || 'fixed',
                min: config.min || 1,
                max: config.max || 100,
                choices: config.choices || [],
                options: Array.isArray(config.choices) ? config.choices.join(', ') : (config.options || '')
            });
        });
    }
    
    // Populate service details
    if (service.details && service.details.length > 0) {
        console.log('Loading service details:', service.details);
        service.details.forEach(detail => {
            addServiceDetail({
                detail_name: detail.detail_name || '',
                price_min: detail.price_min || 0,
                price_max: detail.price_max || 0
            });
        });
    }
    
    // Populate service features
    if (service.features && service.features.length > 0) {
        console.log('Loading service features:', service.features);
        service.features.forEach(feature => {
            addServiceFeature({
                feature_name: feature.feature_name || ''
            });
        });
    }
    
    // Show first tab after loading
    setTimeout(() => {
        document.querySelectorAll('.tab-btn')[0].click();
    }, 100);
}

function deleteService(serviceId) {
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to delete this service? This will also delete all related details and features.';
    document.getElementById('confirmActionBtn').textContent = 'Delete';
    document.getElementById('confirmActionBtn').onclick = function() {
        confirmDelete(serviceId, csrfToken);
    };
    document.getElementById('confirmModal').showModal();
}

function confirmDelete(serviceId, csrfToken) {
    fetch(`services-ajax.php?action=delete&service_id=${serviceId}&csrf_token=${encodeURIComponent(csrfToken)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeConfirmModal();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error deleting service: ' + error, 'error');
        });
}

function closeConfirmModal() {
    document.getElementById('confirmModal').close();
}

// Toast function (reuse from dashboard)
function showToast(message, type = 'info') {
    // Create toast if it doesn't exist
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;padding:12px 16px;border-radius:8px;color:white;z-index:10000;display:none;max-width:300px;word-wrap:break-word;';
        document.body.appendChild(toast);
    }
    
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.background = type === 'error' ? '#dc3545' : 
                            type === 'success' ? '#28a745' : '#17a2b8';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 4000);
}
</script>

<style>
/* Tab Styles */
.tabs {
    display: flex;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
}

.tab-btn {
    padding: 12px 20px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    color: var(--muted);
    transition: all 0.3s ease;
}

.tab-btn:hover {
    color: var(--text);
}

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge.success {
    background: var(--success-light);
    color: var(--success);
}

.badge.info {
    background: var(--info-light);
    color: var(--info);
}

.badge.secondary {
    background: var(--border);
    color: var(--muted);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 4px;
}

.btn.small {
    padding: 6px 8px;
    font-size: 0.875rem;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-header h1 {
    margin: 0;
}

</style>

<!-- Toast -->
<div id="toast" style="position:fixed;bottom:20px;right:20px;padding:12px 16px;border-radius:8px;color:white;z-index:10000;display:none;max-width:300px;word-wrap:break-word;"></div>

<?php include __DIR__ . '/admin-components/admin-footer.php';?>