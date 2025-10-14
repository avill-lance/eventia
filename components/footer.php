<!-- Footer -->
<footer class="d-flex justify-content-center align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>Eventia</h5>
                <p>Creating memorable events with precision and creativity. Let us turn your vision into reality.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.html" class="text-light text-decoration-none">Home</a></li>
                    <li><a href="packages.html" class="text-light text-decoration-none">Packages</a></li>
                    <li><a href="#services" class="text-light text-decoration-none">Services</a></li>
                    <li><a href="shop.html" class="text-light text-decoration-none">Shop</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Support</h5>
                <ul class="list-unstyled">
                    <li><a href="#faq" class="text-light text-decoration-none">FAQ</a></li>
                    <li><a href="#contact" class="text-light text-decoration-none">Contact</a></li>
                </ul>
            </div>
        </div> <!-- Added missing closing div -->
        <hr class="mt-0 mb-4">
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="mb-0">&copy; 2025 Eventia. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript Files -->
<!-- Note: Bootstrap JS is now loaded in header.php -->
<script src="js/jquery-3.7.1.js"></script>
<script src="js/js/datatables.min.js"></script>
<script src="js/sweetalert2@11.js"></script>
<!-- <script src="js/bootstrap.bundle.min.js"></script> -->

<!-- Page-specific JS loading - only load what's needed -->
<?php 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Only load EditProfile.js on profile pages -->
<?php if ($current_page === 'profile.php'): ?>
<script src="js/EditProfile.js"></script>
<?php endif; ?>

<?php if ($current_page === 'transactions.php' || $current_page === 'viewtransactions.php' || isset($load_transactions)): ?>
<script src="js/viewtransactions.js"></script>
<?php endif; ?>

<!-- Only load self_booking.js on booking pages -->
<?php if ($current_page === 'self_booking.php' || $current_page === 'booking.php' || $current_page === 'packages.php'): ?>
<script src="js/self_booking.js"></script>
<?php endif; ?>

<!-- Only load admin.js on admin pages -->
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<script src="js/admin/admin.js"></script>
<?php endif; ?>
</body>
</html>