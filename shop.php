<?php
    // ### Establish Session ###
    include  __DIR__ . "/functions/session.php";
    
    // ### Include functions compilation ###
    include  __DIR__ . "/functions/FunctionCompilation.php";
    isLoggedIn();
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Eventia</title>
    <link rel="icon" type="image/png" href="assets/Logo_BG.png">
    <link rel="stylesheet" href="css/bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation  -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="assets/EventiaLogo.png" alt="" width="50" height="45" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="packages.php">Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blogs.php">Blogs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex ms-auto align-items-center">
                    <a href="cart.php" class="cart-btn-v3 me-3">
                        <i class="bi bi-cart3 cart-icon"></i>
                        <span class="badge">3</span>
                    </a>
                    <a class="nav-link p-0" id="userProfile" href="profile.php">Welcome, <?php echo $_SESSION["first_name"]; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Event Shop</h1>
            <p class="lead mb-4">Find everything you need to make your event special</p>
        </div>
    </div>    

    <!-- Shop Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Categories</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="decorations" checked>
                            <label class="form-check-label" for="decorations">Decorations</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="tableware">
                            <label class="form-check-label" for="tableware">Tableware</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="lighting">
                            <label class="form-check-label" for="lighting">Lighting</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="props">
                            <label class="form-check-label" for="props">Props & Backdrops</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="favors">
                            <label class="form-check-label" for="favors">Party Favors</label>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sort By</h5>
                        <select class="form-select">
                            <option selected>Newest First</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Newest First</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="col-lg-9">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Shop Items</h4>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0">Showing 1-12 of 48 products</p>
                    </div>
                </div>

                <div class="row">
                    <!-- Product 1 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 product-card">
                            <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Product">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success">In Stock</span>
                                    <div class="rating">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </div>
                                </div>
                                <h5 class="card-title">Elegant Centerpieces</h5>
                                <p class="card-text">Set of 6 elegant centerpieces for wedding or formal events.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">₱4,000.00</h4>
                                    <a href="#" class="btn btn-primary">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product 2 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 product-card">
                            <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Product">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success">In Stock</span>
                                    <div class="rating">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                </div>
                                <h5 class="card-title">Chiavari Chairs</h5>
                                <p class="card-text">Premium chiavari chairs for elegant seating at your event.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">₱600.00</h4>
                                    <a href="#" class="btn btn-primary">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product 3 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 product-card">
                            <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Product">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-warning">Low Stock</span>
                                    <div class="rating">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star"></i>
                                    </div>
                                </div>
                                <h5 class="card-title">Fairy Light Backdrop</h5>
                                <p class="card-text">Magical fairy light backdrop perfect for photo opportunities.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">₱7,500.00</h4>
                                    <a href="#" class="btn btn-primary">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product 4 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 product-card">
                            <img src="https://images.unsplash.com/photo-1532117182044-031e7cd916ee?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Product">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success">In Stock</span>
                                    <div class="rating">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </div>
                                </div>
                                <h5 class="card-title">Customized Party Favors</h5>
                                <p class="card-text">Personalized party favors for your guests to remember the event.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">₱150.00</h4>
                                    <a href="#" class="btn btn-primary">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product 5 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 product-card">
                            <img src="https://images.unsplash.com/photo-1560448204-603b3fc33ddc?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Product">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success">In Stock</span>
                                    <div class="rating">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                </div>
                                <h5 class="card-title">Premium Table Linens</h5>
                                <p class="card-text">High-quality table linens available in various colors and sizes.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">₱2,250.00</h4>
                                    <a href="#" class="btn btn-primary">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product 6 -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 product-card">
                            <img src="https://images.unsplash.com/photo-1519677100203-a0e668c92439?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Product">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success">In Stock</span>
                                    <div class="rating">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star"></i>
                                    </div>
                                </div>
                                <h5 class="card-title">Decorative Arch</h5>
                                <p class="card-text">Beautiful decorative arch for ceremonies or photo opportunities.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">₱20,000.00</h4>
                                    <a href="#" class="btn btn-primary">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <nav aria-label="Product pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

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
            <hr class="mt-0 mb-4">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; 2025 Eventia. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

<script src="js/js/bootstrap.bundle.min.js"></script>
</body>
</html>