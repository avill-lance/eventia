// Enhanced services functionality
let selectedServices = [];

function toggleServiceSelection(serviceId) {
    const checkbox = document.getElementById(serviceId);
    const card = checkbox.closest('.service-detail-card');
    
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        card.classList.add('selected');
        if (!selectedServices.includes(serviceId)) {
            selectedServices.push(serviceId);
        }
    } else {
        card.classList.remove('selected');
        selectedServices = selectedServices.filter(id => id !== serviceId);
    }
    
    updateSelectedServicesSummary();
}

function updateSelectedServicesSummary() {
    const summaryContainer = document.getElementById('selectedServicesSummary');
    const servicesList = document.getElementById('selectedServicesList');
    
    if (selectedServices.length > 0) {
        summaryContainer.style.display = 'block';
        servicesList.innerHTML = '';
        
        selectedServices.forEach(serviceId => {
            const service = detailedServices.find(s => s.id === serviceId);
            if (service) {
                const serviceElement = document.createElement('div');
                serviceElement.className = 'd-flex justify-content-between align-items-center mb-2';
                serviceElement.innerHTML = `
                    <div>
                        <strong>${service.name}</strong>
                        <br><small class="text-muted">${service.price_range}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeService('${serviceId}')">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                servicesList.appendChild(serviceElement);
            }
        });
    } else {
        summaryContainer.style.display = 'none';
    }
}

function removeService(serviceId) {
    const checkbox = document.getElementById(serviceId);
    const card = checkbox.closest('.service-detail-card');
    
    checkbox.checked = false;
    card.classList.remove('selected');
    selectedServices = selectedServices.filter(id => id !== serviceId);
    updateSelectedServicesSummary();
}

// Service filtering
document.addEventListener('DOMContentLoaded', function() {
    const filterTags = document.querySelectorAll('.filter-tag');
    
    filterTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active state
            filterTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter services
            const serviceItems = document.querySelectorAll('.service-item');
            serviceItems.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'block';
                } else {
                    const categories = item.getAttribute('data-categories');
                    if (categories.includes(filter)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });
});

// Service detail modal
function showServiceDetails(serviceId) {
    const service = detailedServices.find(s => s.id === serviceId);
    if (service) {
        document.getElementById('serviceModalTitle').textContent = service.name;
        document.getElementById('serviceModalImage').src = service.image;
        
        const modalContent = document.getElementById('serviceModalContent');
        modalContent.innerHTML = `
            <h4 class="text-primary">${service.price_range}</h4>
            <p class="lead">${service.description}</p>
            
            <div class="service-modal-features">
                <div class="service-modal-feature">
                    <i class="bi bi-clock"></i>
                    <h6>Setup Time</h6>
                    <p>2-4 hours</p>
                </div>
                <div class="service-modal-feature">
                    <i class="bi bi-people"></i>
                    <h6>Team Size</h6>
                    <p>2-6 professionals</p>
                </div>
                <div class="service-modal-feature">
                    <i class="bi bi-star"></i>
                    <h6>Rating</h6>
                    <p>4.8/5 stars</p>
                </div>
            </div>
            
            <h5>Package Details</h5>
            <ul class="service-details-list">
                ${service.details.map(detail => `<li>${detail}</li>`).join('')}
            </ul>
            
            <h5 class="mt-4">What's Included</h5>
            <div class="row">
                ${service.features.map(feature => `
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>${feature}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <h6><i class="bi bi-lightbulb me-2"></i>Pro Tip</h6>
                <p class="mb-0">Book this service at least 2 weeks in advance for the best availability and to allow time for customizations.</p>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('serviceDetailModal'));
        modal.show();
    }
}