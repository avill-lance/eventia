<?php include __DIR__."/components/header.php"; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
            <p class="lead mb-4">We'd love to hear from you. Get in touch with our team today.</p>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <h2 class="section-title">Send Us a Message</h2>
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
            <div class="col-md-4">
                <div class="contact-info-card">
                    <h5 class="mb-4">Get In Touch</h5>
                    <div class="d-flex align-items-start mb-4">
                        <i class="bi bi-geo-alt-fill me-3 contact-icon"></i>
                        <div>
                            <h6>Address</h6>
                            <p class="mb-0">123 Event Street, City, Country</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <i class="bi bi-telephone-fill me-3 contact-icon"></i>
                        <div>
                            <h6>Phone</h6>
                            <p class="mb-0">+1 (555) 123-4567</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <i class="bi bi-envelope-fill me-3 contact-icon"></i>
                        <div>
                            <h6>Email</h6>
                            <p class="mb-0">info@eventia.com</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h6>Business Hours</h6>
                        <p class="mb-0">Monday-Friday: 9am - 6pm</p>
                        <p class="mb-0">Saturday: 10am - 4pm</p>
                        <p>Sunday: Closed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="section-title text-center">Frequently Asked Questions</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            How far in advance should I book your services?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We recommend booking our services at least 3-6 months in advance for weddings and large events, and 1-2 months for smaller gatherings. However, we understand that sometimes events come together quickly, so please contact us even if your event is sooner - we'll do our best to accommodate you.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Do you offer virtual event planning?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, we offer comprehensive virtual event planning services. Our team can help you create engaging online experiences with technical support, virtual venue design, interactive elements, and attendee management.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Can you work with my budget?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Absolutely! We pride ourselves on creating exceptional events for a range of budgets. During our initial consultation, we'll discuss your vision and budget to create a customized plan that delivers maximum value without compromising on quality.
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="#faq" class="btn btn-outline-primary">View All FAQs</a>
            </div>
        </div>
    </div>

<?php include __DIR__."/components/footer.php" ?>