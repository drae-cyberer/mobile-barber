<?php
/**
 * User Profile Page
 * 
 * Displays user information, booking history, and account settings
 * Handles different user roles (client, barber, admin)
 */

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    flash_message("You must be logged in to view your profile", "warning");
    header("Location: login.php");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Get user roles
$is_client = has_role('client');
$is_barber = has_role('barber');
$is_admin = has_role('admin');

// Handle profile update
if (isset($_POST['update_profile'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    
    // Validate input
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else if ($email !== $user['email']) {
        // Check if email already exists for another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = db_query($sql, [$email, $user_id]);
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered to another account";
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        // Update user information
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = db_query($sql, [$first_name, $last_name, $email, $phone, $address, $user_id]);
        
        // Update barber profile if applicable
        if ($is_barber && isset($_POST['bio']) && isset($_POST['experience'])) {
            $bio = sanitize_input($_POST['bio']);
            $experience = sanitize_input($_POST['experience']);
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            $sql = "UPDATE barber_profiles SET bio = ?, experience = ?, is_available = ? WHERE user_id = ?";
            $stmt = db_query($sql, [$bio, $experience, $is_available, $user_id]);
        }
        
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/profiles/';
            $filename = upload_file($_FILES['profile_image'], $upload_dir);
            
            if ($filename) {
                // Update profile image in database
                $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
                $stmt = db_query($sql, [$filename, $user_id]);
            }
        }
        
        flash_message("Profile updated successfully", "success");
        header("Location: profile.php");
        exit;
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    
    if (empty($current_password)) {
        $errors[] = "Current password is required";
    } else if (!verify_password($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } else if (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, update password
    if (empty($errors)) {
        $hashed_password = hash_password($new_password);
        
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = db_query($sql, [$hashed_password, $user_id]);
        
        flash_message("Password changed successfully", "success");
        header("Location: profile.php");
        exit;
    }
}

// Get barber profile if applicable
$barber_profile = null;
if ($is_barber) {
    $barber_profile = get_barber_profile($user_id);
}

// Get booking history
$bookings = [];
$booking_sql = "";

if ($is_client) {
    // Get client bookings
    $booking_sql = "SELECT b.*, s.name as service_name, s.price, s.duration, 
                    u.first_name as barber_first_name, u.last_name as barber_last_name, u.profile_image as barber_image 
                    FROM bookings b 
                    JOIN services s ON b.service_id = s.id 
                    JOIN users u ON b.barber_id = u.id 
                    WHERE b.client_id = ? 
                    ORDER BY b.booking_date DESC, b.booking_time DESC";
} elseif ($is_barber) {
    // Get barber bookings
    $booking_sql = "SELECT b.*, s.name as service_name, s.price, s.duration, 
                    u.first_name as client_first_name, u.last_name as client_last_name, u.profile_image as client_image 
                    FROM bookings b 
                    JOIN services s ON b.service_id = s.id 
                    JOIN users u ON b.client_id = u.id 
                    WHERE b.barber_id = ? 
                    ORDER BY b.booking_date DESC, b.booking_time DESC";
} elseif ($is_admin) {
    // Get all bookings for admin
    $booking_sql = "SELECT b.*, s.name as service_name, s.price, s.duration, 
                    c.first_name as client_first_name, c.last_name as client_last_name, c.profile_image as client_image,
                    br.first_name as barber_first_name, br.last_name as barber_last_name, br.profile_image as barber_image 
                    FROM bookings b 
                    JOIN services s ON b.service_id = s.id 
                    JOIN users c ON b.client_id = c.id 
                    JOIN users br ON b.barber_id = br.id 
                    ORDER BY b.booking_date DESC, b.booking_time DESC 
                    LIMIT 50";
}

if (!empty($booking_sql)) {
    $params = ($is_admin) ? [] : [$user_id];
    $stmt = db_query($booking_sql, $params);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Get payment methods
$payment_methods = [];
if ($is_client) {
    $sql = "SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC";
    $stmt = db_query($sql, [$user_id]);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $payment_methods[] = $row;
    }
}
?>

<div class="container mt-4">
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php $flash = get_flash_message(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="<?php echo !empty($user['profile_image']) ? '../uploads/profiles/' . $user['profile_image'] : '../assets/img/default-avatar.png'; ?>" 
                         alt="Profile Image" class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <h5 class="card-title"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
                    <p class="text-muted">
                        <?php if ($is_admin): ?>
                            <span class="badge bg-danger">Admin</span>
                        <?php endif; ?>
                        <?php if ($is_barber): ?>
                            <span class="badge bg-primary">Barber</span>
                        <?php endif; ?>
                        <?php if ($is_client): ?>
                            <span class="badge bg-success">Client</span>
                        <?php endif; ?>
                    </p>
                    <?php if ($is_barber && $barber_profile): ?>
                        <p class="mb-1">
                            <strong>Rating:</strong> 
                            <?php echo number_format($barber_profile['rating'], 1); ?> 
                            <i class="fas fa-star text-warning"></i>
                        </p>
                        <p class="mb-1">
                            <strong>Status:</strong> 
                            <?php if ($barber_profile['is_available']): ?>
                                <span class="text-success">Available</span>
                            <?php else: ?>
                                <span class="text-danger">Unavailable</span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-user me-2"></i> Profile Information
                    </a>
                    <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-key me-2"></i> Change Password
                    </a>
                    <a href="#bookings" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-calendar-alt me-2"></i> Booking History
                    </a>
                    <?php if ($is_client): ?>
                        <a href="#payment" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                            <i class="fas fa-credit-card me-2"></i> Payment Methods
                        </a>
                    <?php endif; ?>
                    <?php if ($is_barber): ?>
                        <a href="#barber-profile" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                            <i class="fas fa-cut me-2"></i> Barber Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Profile Information -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="post" enctype="multipart/form-data">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="tab-pane fade" id="password">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="post">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="text-muted">Password must be at least 8 characters</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Booking History -->
                <div class="tab-pane fade" id="bookings">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Booking History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($bookings)): ?>
                                <p class="text-center">No booking history found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Service</th>
                                                <?php if ($is_client || $is_admin): ?>
                                                    <th>Barber</th>
                                                <?php endif; ?>
                                                <?php if ($is_barber || $is_admin): ?>
                                                    <th>Client</th>
                                                <?php endif; ?>
                                                <th>Status</th>
                                                <th>Price</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($bookings as $booking): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?><br>
                                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                                    </td>
                                                    <td><?php echo $booking['service_name']; ?></td>
                                                    <?php if ($is_client || $is_admin): ?>
                                                        <td>
                                                            <?php if (isset($booking['barber_first_name'])): ?>
                                                                <?php echo $booking['barber_first_name'] . ' ' . $booking['barber_last_name']; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <?php if ($is_barber || $is_admin): ?>
                                                        <td>
                                                            <?php if (isset($booking['client_first_name'])): ?>
                                                                <?php echo $booking['client_first_name'] . ' ' . $booking['client_last_name']; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <?php if ($booking['status'] == 'pending'): ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                                                            <span class="badge bg-primary">Confirmed</span>
                                                        <?php elseif ($booking['status'] == 'completed'): ?>
                                                            <span class="badge bg-success">Completed</span>
                                                        <?php elseif ($booking['status'] == 'cancelled'): ?>
                                                            <span class="badge bg-danger">Cancelled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo format_price($booking['price']); ?></td>
                                                    <td>
                                                        <a href="../booking/details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                                            <?php if ($is_client): ?>
                                                                <a href="../booking/cancel.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($is_barber && $booking['status'] == 'pending'): ?>
                                                                <a href="../booking/confirm.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if ($booking['status'] == 'completed' && $is_client && !$booking['is_rated']): ?>
                                                            <a href="../booking/rate.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-star"></i> Rate
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Methods (Client Only) -->
                <?php if ($is_client): ?>
                <div class="tab-pane fade" id="payment">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Payment Methods</h5>
                            <a href="../payment/add_method.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Add New
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($payment_methods)): ?>
                                <p class="text-center">No payment methods found. Add a payment method to book services.</p>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($payment_methods as $method): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card <?php echo $method['is_default'] ? 'border-primary' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="card-title mb-0">
                                                            <?php if ($method['type'] == 'card'): ?>
                                                                <i class="fas fa-credit-card me-2"></i> 
                                                                <?php echo $method['card_type']; ?> ending in <?php echo $method['last_four']; ?>
                                                            <?php else: ?>
                                                                <i class="fas fa-wallet me-2"></i> 
                                                                <?php echo ucfirst($method['type']); ?>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <?php if ($method['is_default']): ?>
                                                            <span class="badge bg-primary">Default</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($method['type'] == 'card'): ?>
                                                        <p class="card-text small mb-1">Expires: <?php echo $method['expiry_month'] . '/' . $method['expiry_year']; ?></p>
                                                    <?php endif; ?>
                                                    <div class="d-flex justify-content-end mt-3">
                                                        <?php if (!$method['is_default']): ?>
                                                            <a href="../payment/set_default.php?id=<?php echo $method['id']; ?>" class="btn btn-sm btn-outline-primary me-2">Set Default</a>
                                                        <?php endif; ?>
                                                        <a href="../payment/delete_method.php?id=<?php echo $method['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this payment method?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Barber Profile (Barber Only) -->
                <?php if ($is_barber && $barber_profile): ?>
                <div class="tab-pane fade" id="barber-profile">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Barber Profile</h5>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="post">
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo $barber_profile['bio']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="experience" class="form-label">Experience (years)</label>
                                    <input type="number" class="form-control" id="experience" name="experience" value="<?php echo $barber_profile['experience']; ?>" min="0">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_available" name="is_available" <?php echo $barber_profile['is_available'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_available">Available for Bookings</label>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Barber Profile</button>
                            </form>
                            
                            <!-- Barber Portfolio -->
                            <div class="mt-4">
                                <h5>Portfolio</h5>
                                <p class="text-muted">Showcase your work to attract more clients</p>
                                
                                <div class="row">
                                    <?php
                                    $sql = "SELECT * FROM barber_portfolio WHERE barber_id = ?";
                                    $stmt = db_query($sql, [$user_id]);
                                    $result = $stmt->get_result();
                                    $portfolio_items = [];
                                    
                                    while ($row = $result->fetch_assoc()) {
                                        $portfolio_items[] = $row;
                                    }
                                    ?>
                                    
                                    <?php if (empty($portfolio_items)): ?>
                                        <p>No portfolio items yet. Add some to showcase your work.</p>
                                    <?php else: ?>
                                        <?php foreach ($portfolio_items as $item): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <img src="../uploads/portfolio/<?php echo $item['image']; ?>" class="card-img-top" alt="Portfolio Image">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?php echo $item['title']; ?></h6>
                                                        <p class="card-text small"><?php echo $item['description']; ?></p>
                                                        <a href="../barber/delete_portfolio.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this portfolio item?');">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    
                                    <div class="col-12 mt-3">
                                        <a href="../barber/add_portfolio.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Portfolio Item
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>