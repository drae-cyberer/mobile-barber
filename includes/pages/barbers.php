<?php
/**
 * Barbers Page - Display all available barbers with filtering and search
 */

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
$rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$specialty = isset($_GET['specialty']) ? sanitize_input($_GET['specialty']) : '';
$sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'rating';

// Build SQL query with filters
$sql = "SELECT u.*, bp.* FROM users u 
        JOIN barber_profiles bp ON u.id = bp.user_id 
        JOIN user_roles ur ON u.id = ur.user_id 
        WHERE ur.role = 'barber' AND u.status = 'active'";

$params = [];

// Add search filter
if (!empty($search)) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR bp.bio LIKE ? OR bp.specialties LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

// Add location filter
if (!empty($location)) {
    $sql .= " AND bp.location LIKE ?";
    $params[] = "%$location%";
}

// Add rating filter
if ($rating > 0) {
    $sql .= " AND bp.rating >= ?";
    $params[] = $rating;
}

// Add specialty filter
if (!empty($specialty)) {
    $sql .= " AND bp.specialties LIKE ?";
    $params[] = "%$specialty%";
}

// Add sorting
switch ($sort) {
    case 'name':
        $sql .= " ORDER BY u.first_name, u.last_name";
        break;
    case 'price_low':
        $sql .= " ORDER BY bp.hourly_rate ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY bp.hourly_rate DESC";
        break;
    case 'experience':
        $sql .= " ORDER BY bp.experience DESC";
        break;
    default:
        $sql .= " ORDER BY bp.rating DESC, bp.total_ratings DESC";
        break;
}

// Execute query
$stmt = db_query($sql, $params);
$result = $stmt->get_result();

// Get all specialties for filter dropdown
$specialties_sql = "SELECT DISTINCT specialties FROM barber_profiles WHERE specialties IS NOT NULL AND specialties != ''";
$specialties_stmt = db_query($specialties_sql);
$specialties_result = $specialties_stmt->get_result();
$all_specialties = [];
while ($row = $specialties_result->fetch_assoc()) {
    $specs = explode(',', $row['specialties']);
    foreach ($specs as $spec) {
        $spec = trim($spec);
        if (!empty($spec) && !in_array($spec, $all_specialties)) {
            $all_specialties[] = $spec;
        }
    }
}
sort($all_specialties);
?>

<!-- Page Header -->
<div class="hero" style="padding: 60px 0; background: linear-gradient(135deg, var(--primary-color), var(--accent-color));">
    <div class="container">
        <h1 class="fade-in text-white">Find Your Perfect Barber</h1>
        <p class="lead fade-in text-white">Browse through our network of skilled, professional barbers ready to serve you at your location.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3">
                        <input type="hidden" name="page" value="barbers">
                        
                        <!-- Search Input -->
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Barbers</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Name, bio, specialties..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <!-- Location Filter -->
                        <div class="col-md-2">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="City, area..." value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                        
                        <!-- Rating Filter -->
                        <div class="col-md-2">
                            <label for="rating" class="form-label">Min Rating</label>
                            <select class="form-select" id="rating" name="rating">
                                <option value="0">Any Rating</option>
                                <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4+ Stars</option>
                                <option value="4.5" <?php echo $rating == 4.5 ? 'selected' : ''; ?>>4.5+ Stars</option>
                                <option value="5" <?php echo $rating == 5 ? 'selected' : ''; ?>>5 Stars</option>
                            </select>
                        </div>
                        
                        <!-- Specialty Filter -->
                        <div class="col-md-2">
                            <label for="specialty" class="form-label">Specialty</label>
                            <select class="form-select" id="specialty" name="specialty">
                                <option value="">All Specialties</option>
                                <?php foreach ($all_specialties as $spec): ?>
                                <option value="<?php echo htmlspecialchars($spec); ?>" 
                                        <?php echo $spec == $specialty ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($spec); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sort Filter -->
                        <div class="col-md-2">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="experience" <?php echo $sort == 'experience' ? 'selected' : ''; ?>>Most Experience</option>
                            </select>
                        </div>
                        
                        <!-- Filter Button -->
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <?php 
                    $total_barbers = $result->num_rows;
                    echo $total_barbers . ' Barber' . ($total_barbers != 1 ? 's' : '') . ' Found';
                    ?>
                </h4>
                <?php if (!empty($search) || !empty($location) || $rating > 0 || !empty($specialty)): ?>
                <a href="index.php?page=barbers" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Clear Filters
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Barbers Grid -->
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($barber = $result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 d-flex">
                <div class="barber-card flex-fill">
                    <!-- Barber Image -->
                    <div class="position-relative">
                        <img src="<?php echo !empty($barber['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($barber['profile_image']) : 'assets/images/barber-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?>" 
                             class="card-img-top">
                        
                        <!-- Availability Badge -->
                        <?php if ($barber['is_available']): ?>
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">
                            <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Available
                        </span>
                        <?php else: ?>
                        <span class="badge bg-secondary position-absolute top-0 end-0 m-2">
                            <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Busy
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <!-- Barber Name -->
                        <h5 class="card-title mb-2">
                            <?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?>
                        </h5>
                        
                        <!-- Rating -->
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
                            echo ' <span class="text-muted">(' . $barber['total_ratings'] . ' reviews)</span>';
                            ?>
                        </div>
                        
                        <!-- Bio -->
                        <p class="card-text text-muted small mb-2">
                            <?php 
                            echo !empty($barber['bio']) ? 
                                htmlspecialchars(substr($barber['bio'], 0, 120)) . (strlen($barber['bio']) > 120 ? '...' : '') : 
                                'Professional barber with expertise in various styling techniques.'; 
                            ?>
                        </p>
                        
                        <!-- Specialties -->
                        <?php if (!empty($barber['specialties'])): ?>
                        <div class="mb-2">
                            <?php 
                            $specs = explode(',', $barber['specialties']);
                            $display_specs = array_slice($specs, 0, 3); // Show only first 3 specialties
                            foreach ($display_specs as $spec): 
                            ?>
                            <span class="badge bg-light text-dark me-1 mb-1"><?php echo htmlspecialchars(trim($spec)); ?></span>
                            <?php endforeach; ?>
                            <?php if (count($specs) > 3): ?>
                            <span class="badge bg-secondary">+<?php echo count($specs) - 3; ?> more</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Location and Experience -->
                        <div class="row text-muted small mb-3">
                            <?php if (!empty($barber['location'])): ?>
                            <div class="col-6">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($barber['location']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($barber['experience'] > 0): ?>
                            <div class="col-6">
                                <i class="fas fa-award me-1"></i>
                                <?php echo $barber['experience']; ?> experience.
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Price and Book Button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="service-price"><?php echo format_price($barber['hourly_rate']); ?></span>
                                <small class="text-muted">/hour</small>
                            </div>
                            <div>
                                <a href="index.php?page=barbers&action=view&id=<?php echo $barber['user_id']; ?>" 
                                   class="btn btn-outline-primary btn-sm me-1">View Profile</a>
                                <a href="index.php?page=booking&barber=<?php echo $barber['user_id']; ?>" 
                                   class="btn btn-primary btn-sm">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- No Results Found -->
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Barbers Found</h4>
                    <p class="text-muted mb-4">
                        <?php if (!empty($search) || !empty($location) || $rating > 0 || !empty($specialty)): ?>
                            No barbers match your current search criteria. Try adjusting your filters.
                        <?php else: ?>
                            There are currently no barbers available in our system.
                        <?php endif; ?>
                    </p>
                    <a href="index.php?page=barbers" class="btn btn-primary">
                        <i class="fas fa-refresh me-1"></i>Clear All Filters
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination (if needed for large datasets) -->
    <?php if ($result->num_rows > 12): ?>
    <div class="row mt-5">
        <div class="col-12">
            <nav aria-label="Barbers pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Back to Top Button -->
<button id="back-to-top" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4" style="display: none;">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Back to top button functionality
    const backToTopBtn = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });
    
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Auto-submit form on filter change (optional)
    const filterSelects = document.querySelectorAll('#rating, #specialty, #sort');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Uncomment the line below to auto-submit on filter change
            // this.closest('form').submit();
        });
    });
});
</script>