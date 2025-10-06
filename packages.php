<?php include __DIR__."/components/header.php"; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Our Event Packages</h1>
            <p class="lead mb-4">Find the perfect package for your special occasion</p>
        </div>
    </div>

    <!-- Packages Filter -->
    <div class="container my-4">
        <div class="row">
            <div class="col-md-6">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search packages..." aria-label="Search packages">
                    <button class="btn btn-primary" type="button">Search</button>
                </div>
            </div>
            <div class="col-md-6">
                <select class="form-select" aria-label="Filter by category">
                    <option selected>All Categories</option>
                    <option value="1">Weddings</option>
                    <option value="2">Corporate Events</option>
                    <option value="3">Birthdays</option>
                    <option value="4">Anniversaries</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Packages List -->
    <div class="container my-5">
        <div class="row">

            <!-- Package 1 -->
            <div class="col-md-4 mb-4">
                <div class="card package-card h-100">
                    <div class="card-header">Wedding Package</div>
                    <div class="d-flex align-items-center justify-content-center overflow-hidden" style="height: 250px;">
                        <!--Change this-->
                        <img src="https://images.unsplash.com/photo-1519677100203-a0e668c92439?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                            class="img-fluid w-100 h-100" 
                            alt="Wedding Package"
                            style="object-fit: cover;">   
                        <!--End-->
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Elegant Wedding</h5>
                        <p class="card-text">Complete wedding planning with venue decoration, catering, and photography.</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Venue decoration</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Catering for 100 guests</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Professional photography</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Event coordination</li>
                        </ul>
                        <div class="mb-3">
                            <span class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                            </span>
                            <span class="ms-1">4.5/5 (120 reviews)</span>
                        </div>
                        <h4 class="text-primary">₱108,190.00</h4>
                        <a href="#" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#loginRequiredModal">Book Now</a>
                    </div>
                </div>
            </div>
            
            <!-- Package 2 -->
            <div class="col-md-4 mb-4">
                <div class="card package-card h-100">
                    <div class="card-header">Corporate Package</div>
                    <div class="d-flex align-items-center justify-content-center overflow-hidden" style="height: 250px;">
                        <!--Change this-->
                        <img src="https://images.unsplash.com/photo-1531545514256-b1400bc00f31?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                            class="img-fluid w-100 h-100" 
                            alt="Wedding Package"
                            style="object-fit: cover;">   
                        <!--End-->
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Business Events</h5>
                        <p class="card-text">Professional event planning for conferences, seminars, and corporate gatherings.</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Venue selection & setup</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Audio-visual equipment</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Catering services</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Registration management</li>
                        </ul>
                        <div class="mb-3">
                            <span class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                            </span>
                            <span class="ms-1">4/5 (85 reviews)</span>
                        </div>
                        <h4 class="text-primary">₱140,500.00</h4>
                        <a href="#" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#loginRequiredModal">Book Now</a>
                    </div>
                </div>
            </div>
            
            <!-- Package 3 -->
            <div class="col-md-4 mb-4">
                <div class="card package-card h-100">
                    <div class="card-header">Birthday Package</div>
                    <div class="d-flex align-items-center justify-content-center overflow-hidden" style="height: 250px;">
                        <!--Change this-->
                        <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                            class="img-fluid w-100 h-100" 
                            alt="Wedding Package"
                            style="object-fit: cover;">   
                        <!--End-->
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Kids Birthday</h5>
                        <p class="card-text">Fun and creative birthday parties with themes, entertainment, and catering.</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Themed decorations</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Entertainment & games</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Birthday cake & catering</li>
                            <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Party favors</li>
                        </ul>
                        <div class="mb-3">
                            <span class="rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <span class="ms-1">5/5 (210 reviews)</span>
                        </div>
                        <h4 class="text-primary">₱50,000.00</h4>
                        <a href="#" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#loginRequiredModal">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__."/components/footer.php" ?>