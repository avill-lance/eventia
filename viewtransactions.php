<?php include __DIR__."/components/header.php"; ?>

<div class="container d-flex vh-90 gap-4 align-items-center justify-content-center mt-5">
    <div class="card w-100">
        <div class="card-header">
            <h1 class="section-title text-center">Transactions</h1>
        </div>
        <div class="card-body">
            <table id="TransactionsTable" class="display table table-striped" style="width:100%">
                <thead>
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

<script src="js/viewtransactions.js"></script>
<?php include __DIR__."/components/footer.php"; ?>