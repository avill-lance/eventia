<?php include __DIR__."/components/header.php"; ?>

<?php
// Database connection for featured packages
include __DIR__."/database/config.php";

$featuredPackages = [];
try {
    if ($conn) {
        $sql = "SELECT package_id, package_name, package_description, base_price 
                FROM tbl_packages 
                WHERE status = 'active' 
                ORDER BY RAND() 
                LIMIT 3";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $featuredPackages[] = $row;
            }
        }
    }
} catch (Exception $e) {
    // Show nothing as requested if database fails
    $featuredPackages = [];
}

// Database connection for feedback
$approvedFeedback = [];
try {
    if ($conn) {
        $sql = "SELECT f.feedback_id, f.rating, f.title, f.message, 
                       u.first_name, u.last_name 
                FROM tbl_feedback f 
                JOIN tbl_users u ON f.user_id = u.user_id 
                WHERE f.status = 'approved' 
                ORDER BY f.created_at DESC 
                LIMIT 3";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $approvedFeedback[] = $row;
            }
        }
    }
} catch (Exception $e) {
    // Show nothing if database fails
    $approvedFeedback = [];
}

// Function to generate star rating HTML
function generateStarRating($rating) {
    $stars = '';
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    
    // Add full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $stars .= '<i class="bi bi-star-fill"></i>';
    }
    
    // Add half star if needed
    if ($hasHalfStar) {
        $stars .= '<i class="bi bi-star-half"></i>';
        $fullStars++; // Count half star as one for empty stars calculation
    }
    
    // Add empty stars
    $emptyStars = 5 - $fullStars;
    for ($i = 0; $i < $emptyStars; $i++) {
        $stars .= '<i class="bi bi-star"></i>';
    }
    
    return $stars;
}

// Function to generate user initials
function generateUserInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}
?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Create Unforgettable Events</h1>
            <p class="lead mb-4">From intimate gatherings to grand celebrations, we make your events memorable</p>
        </div>
    </div>

    <!-- Featured Packages -->
    <div class="container my-5">
        <h2 class="section-title">Featured Packages</h2>
        <div class="row">
            <?php if (!empty($featuredPackages)): ?>
                <?php foreach ($featuredPackages as $package): ?>
                    <div class="col-md-4">
                        <div class="card package-card">
                            <div class="card-header"><?php echo htmlspecialchars($package['package_name']); ?></div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($package['package_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($package['package_description'] ?? 'Complete event planning package'); ?></p>
                                <h4 class="text-primary">₱<?php echo number_format($package['base_price'], 2); ?></h4>
                                <a href="packages.php" class="btn btn-primary mt-2">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Show nothing as requested if no packages available -->
            <?php endif; ?>
        </div>
    </div>

    <!-- Our Team -->
    <div class="container my-5">
        <h2 class="section-title">Our Team</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/kristan.jpg" alt="Team Member" class="img-fluid">
                    <h5>Kristan Almario</h5>
                    <p>Founder & CEO</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/lance.jpg" alt="Team Member" class="img-fluid">
                    <h5>Lance Villanueva</h5>
                    <p>Event Planner</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/archie.jpeg" alt="Team Member" class="img-fluid">
                    <h5>Archie De Leon</h5>
                    <p>Design Director</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-member">
                    <img src="assets/MemberPhotos/aldis.jpg" alt="Team Member" class="img-fluid">
                    <h5>Aldis Miranda </h5>
                    <p>Marketing Manager</p>
                </div>
            </div>
        </div>
    </div>

    <!-- About Us -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="section-title">Why Choose Eventia?</h2>
            <p class="lead">We started Eventia with a simple mission: to remove the stress from event planning so you can focus on enjoying your special moments. Our team of experienced planners, designers, and coordinators work together to create seamless, memorable events tailored to your vision and budget.</p>
            
        </div>
    </div>

    <!-- Testimonials -->
    <div class="container my-5">
        <h2 class="section-title">Client Reviews</h2>
        <div class="row">
            <?php if (!empty($approvedFeedback)): ?>
                <?php foreach ($approvedFeedback as $feedback): ?>
                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <?php echo generateUserInitials($feedback['first_name'], $feedback['last_name']); ?>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?></h5>
                                    <div class="rating">
                                        <?php echo generateStarRating($feedback['rating']); ?>
                                    </div>
                                </div>
                            </div>
                            <p class="mb-0">"<?php echo htmlspecialchars($feedback['message']); ?>"</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback to static testimonials if no feedback in database -->
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                JL
                            </div>
                            <div>
                                <h5 class="mb-0">Jennifer L.</h5>
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">"Eventia made my wedding day absolutely perfect! Their attention to detail and professional service exceeded all my expectations."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                RT
                            </div>
                            <div>
                                <h5 class="mb-0">Robert T.</h5>
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">"The corporate event they organized for our company was flawless. Everything from venue selection to catering was perfect."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                AP
                            </div>
                            <div>
                                <h5 class="mb-0">Amanda P.</h5>
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">"My daughter's birthday party was magical thanks to Eventia. The theme execution was incredible and the children had a wonderful time."</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include __DIR__."/components/footer.php" ?>