document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Transactions DataTable...');
    
    // Initialize DataTable with correct path
    initializeDataTable();
});

function initializeDataTable() {
    // Clear table
    $('#TransactionsTable tbody').html(`
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Loading transactions...
            </td>
        </tr>
    `);

    try {
        const table = $('#TransactionsTable').DataTable({
            "ajax": {
                "url": "functions/ViewTransactions.php", // ← CORRECT PATH
                "type": "GET",
                "dataType": "json",
                "dataSrc": function (json) {
                    console.log('DataTables received:', json);
                    
                    if (json && json.success) {
                        console.log(`✅ Successfully loaded ${json.data.length} transactions`);
                        return json.data;
                    } else {
                        console.error('❌ API returned error:', json?.message);
                        showErrorMessage(json?.message || 'Failed to load transactions');
                        return [];
                    }
                },
                "error": function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    console.log('Response preview:', xhr.responseText?.substring(0, 200));
                    
                    if (xhr.status === 404) {
                        showErrorMessage('API endpoint not found. Check the file path.');
                    } else if (xhr.responseText && xhr.responseText.includes('<!DOCTYPE')) {
                        showErrorMessage('Server returned HTML instead of JSON. Check PHP execution.');
                    } else {
                        showErrorMessage('Network error: ' + error);
                    }
                }
            },
            "columns": [
                { 
                    "data": "transaction_id",
                    "className": "fw-bold"
                },
                { 
                    "data": "ref_id",
                    "className": "text-muted"
                },
                { 
                    "data": "date_time",
                    "render": function(data) {
                        if (!data) return 'N/A';
                        try {
                            const date = new Date(data);
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        } catch (e) {
                            return data;
                        }
                    }
                },
                { 
                    "data": "status",
                    "render": function(data) {
                        const statusMap = {
                            'completed': 'status-success',
                            'success': 'status-success',
                            'paid': 'status-success',
                            'pending': 'status-pending',
                            'failed': 'status-failed',
                            'cancelled': 'status-failed',
                            'processing': 'status-processing'
                        };
                        
                        const statusClass = statusMap[data?.toLowerCase()] || 'status-pending';
                        const statusText = data?.charAt(0).toUpperCase() + data?.slice(1).toLowerCase() || 'Pending';
                        
                        return `<span class="status-badge ${statusClass}">${statusText}</span>`;
                    }
                },
                { 
                    "data": "price",
                    "render": function(data) {
                        if (!data) return '₱0.00';
                        const amount = parseFloat(data);
                        if (isNaN(amount)) return '₱0.00';
                        return `<span class="price-amount">₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
                    },
                    "className": "text-end"
                },
                { 
                    "data": "transaction_id",
                    "render": function(data, type, row) {
                        let buttons = `
                            <button class="btn btn-action btn-view" onclick="viewTransaction('${data}')" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        `;
                        
                        if (row.status && row.status.toLowerCase() === 'pending') {
                            buttons += `
                                <button class="btn btn-action btn-cancel" onclick="cancelTransaction('${data}')" title="Cancel">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            `;
                        }
                        
                        return `<div class="d-flex justify-content-center gap-1">${buttons}</div>`;
                    },
                    "orderable": false,
                    "searchable": false,
                    "className": "text-center"
                }
            ],
            "language": {
                "emptyTable": "No transactions found",
                "zeroRecords": "No matching transactions found",
                "info": "Showing _START_ to _END_ of _TOTAL_ transactions",
                "infoEmpty": "Showing 0 to 0 of 0 transactions",
                "infoFiltered": "(filtered from _MAX_ total transactions)",
                "search": "Search transactions:",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                },
                "lengthMenu": "Show _MENU_ transactions",
                "processing": "Loading transactions..."
            },
            "order": [[0, 'desc']],
            "responsive": true,
            "processing": true,
            "serverSide": false,
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": 10
        });

        // Refresh button
        $('#refreshTable').on('click', function() {
            const $btn = $(this);
            const originalHtml = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="bi bi-arrow-clockwise me-1"></i> Refreshing...');
            
            table.ajax.reload(function() {
                $btn.prop('disabled', false).html(originalHtml);
            });
        });
        
    } catch (error) {
        console.error('DataTable initialization error:', error);
        showErrorMessage('Error initializing table: ' + error.message);
    }
}

function showErrorMessage(message) {
    $('#TransactionsTable tbody').html(`
        <tr>
            <td colspan="6" class="text-center text-danger py-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
            </td>
        </tr>
    `);
}

// Global functions
function viewTransaction(transactionId) {
    console.log('View transaction:', transactionId);
    alert('View transaction: ' + transactionId);
}

function cancelTransaction(transactionId) {
    if (confirm('Are you sure you want to cancel this transaction?')) {
        console.log('Cancel transaction:', transactionId);
        alert('Cancel transaction: ' + transactionId);
    }
}