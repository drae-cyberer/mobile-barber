<?php
/**
 * Services Page - Mobile Barber Platform
 * Displays all available services with filtering and search functionality
 */

// Get all services from database
$services = get_services();

// Group services by category for better organization
$services_by_category = [];
foreach ($services as $service) {
    $services_by_category[$service['category']][] = $service;
}

// Get unique categories for filter buttons
$categories = array_keys($services_by_category);
?>

<div class="hero-section py-5" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white mb-3 fade-in">Professional Barbing Services</h1>
                <p class="text-white-50 lead mb-4 fade-in">From classic cuts to modern styles, our skilled barbers deliver premium grooming services at your convenience.</p>
                <div class="fade-in">
                    <a href="index.php?page=booking" class="btn btn-secondary btn-lg me-3">
                        <i class="fas fa-calendar-check me-2"></i>Book Now
                    </a>
                    <a href="index.php?page=barbers" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-users me-2"></i>Meet Our Barbers
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="service-hero-icon">
                    <i class="fas fa-cut fa-8x text-white opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Service Statistics -->
    <div class="row mb-5">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card text-center p-4 bg-white rounded-lg shadow-sm">
                <div class="stat-icon mb-3">
                    <i class="fas fa-cut fa-2x text-primary"></i>
                </div>
                <h3 class="h4 mb-2"><?php echo count($services); ?>+</h3>
                <p class="text-muted mb-0">Premium Services</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card text-center p-4 bg-white rounded-lg shadow-sm">
                <div class="stat-icon mb-3">
                    <i class="fas fa-clock fa-2x text-success"></i>
                </div>
                <h3 class="h4 mb-2">30min</h3>
                <p class="text-muted mb-0">Average Service Time</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card text-center p-4 bg-white rounded-lg shadow-sm">
                <div class="stat-icon mb-3">
                    <i class="fas fa-home fa-2x text-warning"></i>
                </div>
                <h3 class="h4 mb-2">100%</h3>
                <p class="text-muted mb-0">Mobile Service</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card text-center p-4 bg-white rounded-lg shadow-sm">
                <div class="stat-icon mb-3">
                    <i class="fas fa-star fa-2x text-warning"></i>
                </div>
                <h3 class="h4 mb-2">4.8</h3>
                <p class="text-muted mb-0">Average Rating</p>
            </div>
        </div>
    </div>

    <!-- Service Categories Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-section bg-white p-4 rounded-lg shadow-sm">
                <h5 class="mb-3">Filter by Category</h5>
                <div class="category-filters">
                    <button class="btn btn-outline-primary active me-2 mb-2" data-filter="all">
                        <i class="fas fa-th me-1"></i>All Services
                    </button>
                    <?php foreach ($categories as $category): ?>
                    <button class="btn btn-outline-primary me-2 mb-2" data-filter="<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                        <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($category); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Grid -->
    <?php foreach ($services_by_category as $category => $category_services): ?>
    <section class="mb-5 service-category" data-category="<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
        <div class="d-flex align-items-center mb-4">
            <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
            <div class="category-line flex-grow-1 ms-3"></div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($category_services as $service): ?>
            <div class="col-xl-4 col-lg-6 col-md-6 d-flex">
                <div class="service-card enhanced flex-fill" data-category="<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                    <div class="service-image-container">
                        <img src="<?php echo !empty($service['image']) ? 'uploads/services/' . htmlspecialchars($service['image']) : 'assets/images/service-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($service['name']); ?>" 
                             class="service-image">
                        <div class="service-overlay">
                            <div class="service-actions">
                                <a href="index.php?page=booking&service=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-calendar-check me-1"></i>Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="service-title mb-0"><?php echo htmlspecialchars($service['name']); ?></h5>
                            <span class="service-price-badge"><?php echo format_price($service['price']); ?></span>
                        </div>
                        
                        <p class="service-description text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                        
                        <div class="service-details">
                            <div class="row">
                                <div class="col-6">
                                    <div class="detail-item">
                                        <i class="far fa-clock text-primary me-2"></i>
                                        <span><?php echo htmlspecialchars($service['duration']); ?> mins</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <i class="fas fa-home text-success me-2"></i>
                                        <span>Mobile Service</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="service-features mt-3">
                            <div class="d-flex flex-wrap gap-1">
                                <span class="feature-badge">Professional Tools</span>
                                <span class="feature-badge">Sanitized Equipment</span>
                                <span class="feature-badge">Expert Stylists</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

    <!-- No Services Found Message -->
    <div id="no-services-found" class="text-center py-5" style="display: none;">
        <div class="no-results-container">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Services Found</h4>
            <p class="text-muted">Try adjusting your filters or browse all services.</p>
            <button class="btn btn-primary" onclick="showAllServices()">Show All Services</button>
        </div>
    </div>

    <!-- Why Choose Our Services -->
    <section class="py-5 bg-light rounded-lg mt-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Why Choose Our Services?</h2>
                <p class="lead text-muted">We bring professional barbing directly to your location</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-home fa-3x text-primary"></i>
                        </div>
                        <h5>Convenient Location</h5>
                        <p class="text-muted">We come to your home, office, or any location you prefer. No travel time, no waiting rooms.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-users fa-3x text-success"></i>
                        </div>
                        <h5>Expert Barbers</h5>
                        <p class="text-muted">All our barbers are licensed professionals with years of experience and continuous training.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-warning"></i>
                        </div>
                        <h5>Safe & Sanitized</h5>
                        <p class="text-muted">We follow strict hygiene protocols and use only sanitized, professional-grade equipment.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-clock fa-3x text-info"></i>
                        </div>
                        <h5>Flexible Scheduling</h5>
                        <p class="text-muted">Book appointments that fit your schedule, including evenings and weekends.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-dollar-sign fa-3x text-success"></i>
                        </div>
                        <h5>Transparent Pricing</h5>
                        <p class="text-muted">Clear, upfront pricing with no hidden fees. Pay securely online or in cash.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-star fa-3x text-warning"></i>
                        </div>
                        <h5>Quality Guarantee</h5>
                        <p class="text-muted">Not satisfied? We'll make it right. Your satisfaction is our top priority.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="text-center py-5 mt-5">
        <div class="cta-container bg-primary rounded-lg p-5 text-white">
            <h2 class="mb-3">Ready to Book Your Service?</h2>
            <p class="lead mb-4">Choose from our wide range of professional barbing services and get styled at your convenience.</p>
            <div class="cta-buttons">
                <a href="index.php?page=booking" class="btn btn-secondary btn-lg me-3">
                    <i class="fas fa-calendar-check me-2"></i>Book Appointment
                </a>
                <a href="index.php?page=barbers" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-users me-2"></i>View Barbers
                </a>
            </div>
        </div>
    </section>
</div>

<!-- Enhanced CSS for Services Page -->
<style>
.hero-section {
    position: relative;
    overflow: hidden;
}

.service-hero-icon {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.stat-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.category-filters .btn {
    transition: all 0.3s ease;
    border-radius: 25px;
    font-weight: 500;
}

.category-filters .btn.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    transform: scale(1.05);
}

.category-title {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 0;
}

.category-line {
    height: 2px;
    background: linear-gradient(to right, var(--primary-color), transparent);
}

.service-card.enhanced {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.4s ease;
    background: white;
}

.service-card.enhanced:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.service-image-container {
    position: relative;
    overflow: hidden;
}

.service-image {
    height: 220px;
    width: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.service-card.enhanced:hover .service-image {
    transform: scale(1.1);
}

.service-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(44,62,80,0.8), rgba(52,152,219,0.8));
    opacity: 0;
    transition: all 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.service-card.enhanced:hover .service-overlay {
    opacity: 1;
}

.service-price-badge {
    background: linear-gradient(135deg, var(--secondary-color), #c0392b);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
}

.service-title {
    color: var(--dark-color);
    font-weight: 600;
}

.service-description {
    font-size: 0.95rem;
    line-height: 1.5;
    min-height: 3rem;
}

.detail-item {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.feature-badge {
    background-color: rgba(52,152,219,0.1);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.feature-card {
    background: white;
    border-radius: 10px;
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.cta-container {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color)) !important;
}

.service-category {
    transition: all 0.5s ease;
}

.service-category.hidden {
    display: none;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .service-card.enhanced {
        margin-bottom: 2rem;
    }
    
    .hero-section .col-lg-4 {
        display: none;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .category-filters .btn {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
    }
}
</style>

<!-- JavaScript for Service Filtering -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Service filtering functionality
    const filterButtons = document.querySelectorAll('[data-filter]');
    const serviceCategories = document.querySelectorAll('.service-category');
    const noServicesMessage = document.getElementById('no-services-found');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter services
            let visibleCount = 0;
            
            serviceCategories.forEach(category => {
                if (filter === 'all' || category.getAttribute('data-category') === filter) {
                    category.style.display = 'block';
                    category.classList.remove('hidden');
                    visibleCount++;
                } else {
                    category.style.display = 'none';
                    category.classList.add('hidden');
                }
            });
            
            // Show/hide no results message
            if (visibleCount === 0) {
                noServicesMessage.style.display = 'block';
            } else {
                noServicesMessage.style.display = 'none';
            }
        });
    });
});

function showAllServices() {
    const allButton = document.querySelector('[data-filter="all"]');
    allButton.click();
}
</script>