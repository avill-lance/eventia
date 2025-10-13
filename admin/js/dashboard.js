// Dashboard functionality
$(document).ready(function() {
    loadDashboardStats();
    loadRecentActivity();
});

function loadDashboardStats() {
    $.ajax({
        url: 'api/dashboard-stats.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#totalUsers').text(response.data.totalUsers);
                $('#totalBookings').text(response.data.totalBookings);
                $('#totalPackages').text(response.data.totalPackages);
                $('#totalRevenue').text('₱' + response.data.totalRevenue.toLocaleString());
            }
        },
        error: function() {
            showToast('Error loading dashboard statistics', 'error');
        }
    });
}

function loadRecentActivity() {
    $.ajax({
        url: 'api/recent-activity.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '';
                if (response.data.length === 0) {
                    html = '<div class="muted" style="text-align: center; padding: 20px;">No recent activity</div>';
                } else {
                    response.data.forEach(activity => {
                        html += `
                            <div style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; justify-content: between; align-items: center;">
                                    <strong>${activity.title}</strong>
                                    <span class="muted" style="font-size: 12px;">${activity.time}</span>
                                </div>
                                <div class="muted" style="font-size: 14px;">${activity.description}</div>
                            </div>
                        `;
                    });
                }
                $('#recentActivity').html(html);
            }
        },
        error: function() {
            $('#recentActivity').html('<div class="muted" style="text-align: center; padding: 20px;">Error loading activities</div>');
        }
    });
}