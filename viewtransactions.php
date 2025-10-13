<?php include __DIR__."/components/header.php"; ?>

<link rel="stylesheet" href="css/viewtransactions.css">

<div class="container d-flex vh-90 gap-4 align-items-center justify-content-center mt-5">
    <div class="card w-100">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="section-title">Transactions</h1>
                <button class="btn btn-refresh" id="refreshTable">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="TransactionsTable" class="display table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Reference ID</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include DataTables CSS -->
<link rel="stylesheet" type="text/css" href="css/datatables.min.css">
<?php include __DIR__."/components/footer.php"; ?>