<?php
/**
 * Search Page
 * 
 * Handles search functionality for the Mobile Barber platform
 */

// Get search query from URL parameter
$search_query = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Initialize arrays to store search results
$services_results = [];
$barbers_results = [];

// Only perform search if query is not empty
if (!empty($search_query)) {
    // Search in services
    $sql_services = "SELECT * FROM services WHERE 
                    name LIKE ? OR 
                    description LIKE ? OR 
                    category LIKE ? 
                    ORDER BY category, name";
    
    $search_param = "%{$search_query}%";
    $stmt = db_query($sql_services, [$search_param, $search_param, $search_param]);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $services_results[] = $row;
    }
    
    // Search in barbers
    $sql_barbers = "SELECT u.*, bp.* FROM users u 
                JOIN barber_profiles bp ON u.id = bp.user_id 
                JOIN user_roles ur ON u.id = ur.user_id 
                WHERE ur.role = 'barber' AND u.status = 'active' AND 
                (u.first_name LIKE ? OR 
                u.last_name LIKE ? OR 
                bp.bio LIKE ?)
                ORDER BY bp.rating DESC";
    
    $stmt = db_query($sql_barbers, [$search_param, $search_param, $search_param]);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $barbers_results[] = $row;
    }
}
?>

<div class="container py-5">
    <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
    
    <?php if (empty($search_query)): ?>
        <div class="alert alert-info">
            <p>Please enter a search term to find services, barbers, or other information.</p>
        </div>
    <?php elseif (empty($services_results) && empty($barbers_results)): ?>
        <div class="alert alert-warning">
            <p>No results found for "<?php echo htmlspecialchars($search_query); ?>". Please try a different search term.</p>
        </div>
    <?php else: ?>
        <!-- Services Results -->
        <?php if (!empty($services_results)): ?>
            <section class="mb-5">
                <h2>Services (<?php echo count($services_results); ?>)</h2>
                <div class="row g-4">
                    <?php foreach ($services_results as $service): ?>
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
                    <?php endforeach; ?>
                </div>
                <div class="mt-3">
                    <a href="index.php?page=services" class="btn btn-outline-primary">View All Services</a>
                </div>
            </section>
        <?php endif; ?>
        
        <!-- Barbers Results -->
        <?php if (!empty($barbers_results)): ?>
            <section class="mb-5">
                <h2>Barbers (<?php echo count($barbers_results); ?>)</h2>
                <div class="row g-4">
                    <?php foreach ($barbers_results as $barber): ?>
                        <div class="col-md-4 d-flex">
                            <div class="barber-card flex-fill">
                                <div class="barber-image">
                                    <img src="<?php echo !empty($barber['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($barber['profile_image']) : 'assets/images/profile-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?>" class="img-fluid">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']); ?></h5>
                                    <div class="barber-rating mb-2">
                                        <?php 
                                        $rating = $barber['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } elseif ($i - 0.5 <= $rating) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        echo " <span>({$barber['total_ratings']})</span>";
                                        ?>
                                    </div>
                                    <p class="card-text"><?php echo htmlspecialchars($barber['bio']); ?></p>
                                    <p><strong>Specialties:</strong> <?php echo htmlspecialchars($barber['specialties']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($barber['service_area']); ?></span>
                                        <a href="index.php?page=booking&barber=<?php echo $barber['user_id']; ?>" class="btn btn-sm btn-primary">Book Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3">
                    <a href="index.php?page=barbers" class="btn btn-outline-primary">View All Barbers</a>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>