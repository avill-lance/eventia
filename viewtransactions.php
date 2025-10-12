<?php include __DIR__."/components/header.php"; ?>

<div class="container d-flex vh-90 gap-4 align-items-center justify-content-center mt-5">
    <div class="card w-100">
        <div class="card-header">
            <h1 class="section-title text-center">Transactions</h1>
        </div>
        <div class="card-body">
            <table id="TransactionsTable" class="display table table-striped" style="width:100%">
                <thead>
                    <!-- Columns will be loaded dynamically -->
                </thead>
                <tbody>
                    <!-- Data will be loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<!-- Set a flag to indicate this is a transactions page -->
<script>
    // Set a global flag to indicate this is a transactions page
    window.isTransactionsPage = true;
</script>

<?php include __DIR__."/components/footer.php"; ?>