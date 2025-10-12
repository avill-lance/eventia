$(document).ready(function(){
    console.log('🔄 Starting AJAX request...');
    
    $.ajax({
        url: 'functions/ViewTransactions.php',
        method: "GET",
        dataType: "json",
        success: function(response){
            console.log('✅ AJAX Success - Full response:', response);
            
            if(response.success && response.data) {
                console.log('✅ Data loaded successfully');
                console.log('Number of transactions:', response.data.length);
                console.log('Actual columns:', response.debug.actual_columns);
                
                // Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable('#TransactionsTable')) {
                    $('#TransactionsTable').DataTable().destroy();
                }
                
                // Clear static data
                $('#TransactionsTable tbody').empty();
                
                if(response.data.length > 0) {
                    // Initialize DataTable with dynamic column detection
                    initializeDataTable(response.data);
                } else {
                    console.log('No transactions found for user');
                    initializeEmptyTable('No transactions found');
                }
                
            } else {
                console.error('No data received:', response);
                alert('Error: ' + (response.message || 'No transactions found'));
                initializeEmptyTable('No transactions available');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            alert('Failed to load transactions. Please check console for details.');
            initializeEmptyTable('Failed to load data');
        }
    });
    
    function initializeDataTable(data) {
        // Auto-detect columns from the first data row
        var columns = [];
        if(data.length > 0) {
            var firstRow = data[0];
            
            // Map common column names
            Object.keys(firstRow).forEach(function(key) {
                var title = key.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                    return l.toUpperCase();
                });
                
                columns.push({
                    data: key,
                    title: title
                });
            });
        }
        
        console.log('Generated columns:', columns);
        
        // Initialize DataTable
        $('#TransactionsTable').DataTable({
            data: data,
            columns: columns,
            order: [[0, 'desc']],
            responsive: true
        });
    }
    
    function initializeEmptyTable(message) {
        $('#TransactionsTable').DataTable({
            language: {
                emptyTable: message
            },
            responsive: true
        });
    }
});