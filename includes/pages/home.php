<div class="hero">
    <div class="container">
        <h1 class="fade-in">Professional Barbing at Your Doorstep</h1>
        <p class="lead fade-in">Book a skilled barber to come to your location with just a few clicks. Quality haircuts and styling services delivered to you.</p>
        <div class="mt-4 fade-in">
            <a href="index.php?page=booking" class="btn btn-secondary btn-lg me-2">Book Now</a>
            <a href="index.php?page=services" class="btn btn-outline-light btn-lg">Our Services</a>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- How It Works Section -->
    <section class="mb-5">
        <h2 class="mb-4">How It Works</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-container mb-3">
                            <i class="fas fa-search fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Find a Barber</h5>
                        <p class="card-text">Browse through our selection of top-rated barbers and find the perfect match for your style needs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-container mb-3">
                            <i class="far fa-calendar-check fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Book an Appointment</h5>
                        <p class="card-text">Select your preferred date and time, and book your appointment with just a few clicks.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-container mb-3">
                            <i class="fas fa-cut fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Get Your Service</h5>
                        <p class="card-text">Sit back and relax as our skilled barbers provide you with top-notch service.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile See More Button -->
        <div class="mobile-see-more">
            <a href="index.php?page=about" class="btn btn-primary btn-lg rounded-pill px-4 py-2 mt-3">
                <i class="fas fa-info-circle me-2"></i>Learn More
            </a>
        </div>
    </section>

    <!-- Featured Services Section -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Featured Services</h2>
            <a href="index.php?page=services" class="btn btn-outline-primary desktop-view-all">View All Services</a>
        </div>
        <div class="row g-4">
            <?php
            // Get featured services (limit to 3)
            $sql = "SELECT * FROM services WHERE status = 'active' ORDER BY id LIMIT 3";
            $stmt = db_query($sql);
            $result = $stmt->get_result();
            
            while ($service = $result->fetch_assoc()):
            ?>
            <div class="col-md-4 d-flex">
                <div class="service-card flex-fill" data-category="<?php echo htmlspecialchars($service['category']); ?>">
                    <img src="<?php echo !empty($service['image']) ? 'uploads/services/' . htmlspecialchars($service['image']) : 'assets/images/service-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($service['name']); ?>" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="service-price mb-2"><?php echo format_price($service['price']); ?></p>
                        <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="far fa-clock me-1"></i> <?php echo htmlspecialchars($service['duration']); ?> mins</span>
                            <a href="index.php?page=booking&service=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <!-- Mobile See More Button -->
        <div class="mobile-see-more">
            <a href="index.php?page=services" class="btn btn-primary btn-lg rounded-pill px-4 py-2 mt-3">
                <i class="fas fa-cut me-2"></i>See More Services
            </a>
        </div>
    </section>
    

    <!-- Top Barbers Section -->
    <?php
    // Get top rated barbers (limit to 3, only barbers with at least 3 ratings)
    $sql = "SELECT u.*, bp.* FROM users u 
            JOIN barber_profiles bp ON u.id = bp.user_id 
            JOIN user_roles ur ON u.id = ur.user_id 
            WHERE ur.role = 'barber' AND u.status = 'active' 
            AND bp.total_ratings >= 3
            ORDER BY bp.rating DESC LIMIT 3";
    $stmt = db_query($sql);
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
    ?>
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Top Barbers</h2>
            <a href="index.php?page=barbers" class="btn btn-outline-primary">View All Barbers</a>
        </div>
        <div class="row g-4">
            <?php
            while ($barber = $result->fetch_assoc()):
            ?>
            <div class="col-md-4 d-flex">
                <div class="barber-card flex-fill" data-rating="<?php echo $barber['rating']; ?>">
                    <img src="<?php echo !empty($barber['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($barber['profile_image']) : 'assets/images/barber-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?>" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?></h5>
                        <div class="barber-rating mb-2">
                            <?php
                            $rating = round($barber['rating'] * 2) / 2; // Round to nearest 0.5
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i - 0.5 == $rating) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            echo ' <span>(' . $barber['total_ratings'] . ')</span>';
                            ?>
                        </div>
                        <p class="card-text"><?php echo !empty($barber['bio']) ? htmlspecialchars(substr($barber['bio'], 0, 100)) . '...' : 'Professional barber with expertise in various styles.'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-dollar-sign me-1"></i> <?php echo format_price($barber['hourly_rate']); ?>/hr</span>
                            <a href="index.php?page=booking&barber=<?php echo $barber['user_id']; ?>" class="btn btn-sm btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <section class="mb-5 testimonials">
        <h2 class="text-center mb-4">What Our Customers Say</h2>
        <div class="testimonial-container">
            <div class="row g-4 testimonial-container">
                <div class="col-md-4 d-flex testimonial-item" data-index="0">
                    <div class="service-card testimonial-card flex-fill">
                        <div class="testimonial-header">
                            <div class="barber-rating mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text">"The mobile barber service was incredible! They arrived on time, were professional, and gave me a fantastic haircut right in my home."</p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/testimonial-1.jpg" alt="Customer" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <h6 class="mb-0 fw-bold">Soma Charles</h6>
                                        <small class="text-muted">Regular Customer</small>
                                    </div>
                                </div>
                                <span class="badge bg-primary">Verified</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex testimonial-item" data-index="1">
                    <div class="service-card testimonial-card flex-fill">
                        <div class="testimonial-header">
                            <div class="barber-rating mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text">"I was skeptical at first, but the barber was professional, on time, and gave me one of the best haircuts I've ever had."</p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/testimonial-2.jpg" alt="Customer" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <h6 class="mb-0 fw-bold">Michael Jeffery</h6>
                                        <small class="text-muted">New Customer</small>
                                    </div>
                                </div>
                                <span class="badge bg-primary">Verified</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex testimonial-item" data-index="2">
                    <div class="service-card testimonial-card flex-fill">
                        <div class="testimonial-header">
                            <div class="barber-rating mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text">"As a busy professional, this service saves me so much time. The online booking is seamless and the barbers are top-notch."</p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/testimonial-3.jpg" alt="Customer" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <h6 class="mb-0 fw-bold">David Gabriella</h6>
                                        <small class="text-muted">Regular Customer</small>
                                    </div>
                                </div>
                                <span class="badge bg-primary">Verified</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Enhanced testimonial navigation (replace existing nav section) -->
          <div class="d-flex justify-content-center mt-4">
             <div class="testimonial-nav">
                       <button class="btn btn-sm btn-outline-primary me-2 prev-testimonial" aria-label="Previous testimonial">
                          <i class="fas fa-chevron-left"></i> Previous
                        </button>
                    <button class="btn btn-sm btn-primary next-testimonial" aria-label="Next testimonial">
                       Next <i class="fas fa-chevron-right"></i>
                     </button>
              </div>
            </div>
        <div class="testimonial-pagination d-flex justify-content-center mt-3">
            <span class="testimonial-dot active" data-index="0"></span>
            <span class="testimonial-dot" data-index="1"></span>
            <span class="testimonial-dot" data-index="2"></span>
       </div>
     </div> 
 </section>

    <!-- Call to Action -->
    <section class="text-center py-5 bg-primary-light rounded-lg">
        <h2 class="mb-3">Ready for a Fresh Look?</h2>
        <p class="lead mb-4">Book your barbing service now and experience professional grooming at your convenience.</p>
        <a href="index.php?page=booking" class="btn btn-primary btn-lg">Book Your Appointment</a>
    </section>
</div>

<!-- Back to Top Button -->
<button id="back-to-top" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4" style="display: none;">
    <i class="fas fa-arrow-up"></i>
</button>

