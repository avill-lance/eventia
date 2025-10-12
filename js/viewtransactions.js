$(document).ready(function(){
    console.log('🔍 Checking if transactions should load...');
    
    // Multiple conditions to detect transactions pages
    const isTransactionsPage = 
        window.isTransactionsPage === true || 
        $('#TransactionsTable').length > 0 ||
        window.location.pathname.includes('transactions') ||
        document.title.toLowerCase().includes('transaction');
    
    console.log('📄 Page detection results:', {
        windowFlag: window.isTransactionsPage,
        tableExists: $('#TransactionsTable').length > 0,
        urlHasTransactions: window.location.pathname.includes('transactions'),
        titleHasTransaction: document.title.toLowerCase().includes('transaction'),
        finalDecision: isTransactionsPage
    });
    
    if (isTransactionsPage) {
        console.log('✅ Transactions page detected, loading data...');
        loadTransactionsData();
    } else {
        console.log('⏸️ Not a transactions page, skipping data load');
    }
    
    function loadTransactionsData() {
        console.log('🔄 Starting AJAX request for transactions...');
        
        // Check if DataTables is available
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('❌ DataTables not loaded yet, waiting...');
            // Retry after a short delay
            setTimeout(loadTransactionsData, 100);
            return;
        }
        
        $.ajax({
            url: 'functions/ViewTransactions.php',
            method: "GET",
            dataType: "json",
            success: function(response){
                console.log('✅ AJAX Success - Full response:', response);
                
                if(response.success && response.data) {
                    console.log('✅ Data loaded successfully');
                    console.log('Number of transactions:', response.data.length);
                    
                    // Destroy existing DataTable if it exists
                    if ($.fn.DataTable.isDataTable('#TransactionsTable')) {
                        $('#TransactionsTable').DataTable().destroy();
                        console.log('♻️ Destroyed existing DataTable');
                    }
                    
                    // Clear static data
                    $('#TransactionsTable tbody').empty();
                    
                    if(response.data.length > 0) {
                        console.log('📊 Initializing DataTable with data...');
                        // Initialize DataTable with dynamic column detection
                        initializeDataTable(response.data);
                    } else {
                        console.log('ℹ️ No transactions found for user');
                        initializeEmptyTable('No transactions found');
                    }
                    
                } else {
                    console.error('❌ No data received:', response);
                    initializeEmptyTable('No transactions available');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                initializeEmptyTable('Failed to load transactions data');
            }
        });
    }
    
    function initializeDataTable(data) {
        try {
            // Auto-detect columns from the first data row
            var columns = [];
            if(data.length > 0) {
                var firstRow = data[0];
                
                console.log('📋 First row data for column detection:', firstRow);
                
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
            
            console.log('🔄 Generated columns:', columns);
            
            // Initialize DataTable
            $('#TransactionsTable').DataTable({
                data: data,
                columns: columns,
                order: [[0, 'desc']],
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "No transactions available",
                    zeroRecords: "No matching transactions found"
                }
            });
            
            console.log('✅ DataTable initialized successfully');
            
        } catch (error) {
            console.error('❌ DataTable initialization failed:', error);
            initializeEmptyTable('Error initializing table');
        }
    }
    
    function initializeEmptyTable(message) {
        try {
            $('#TransactionsTable').DataTable({
                language: {
                    emptyTable: message
                },
                responsive: true,
                pageLength: 10
            });
            console.log('✅ Empty table initialized with message:', message);
        } catch (error) {
            console.error('❌ Failed to initialize empty table:', error);
            $('#TransactionsTable').html('<tr><td colspan="10" class="text-center">' + message + '</td></tr>');
        }
    }
});