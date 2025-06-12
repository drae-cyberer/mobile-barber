<?php
/**
 * Common functions for the Mobile Barber Platform
 */

/**
 * Sanitize user input
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate a secure password hash
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches hash
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 * 
 * @param string $role Role to check (client, barber, admin)
 * @return bool True if user has role
 */
function has_role($role) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM user_roles WHERE user_id = ? AND role = ?";
    $stmt = db_query($sql, [$user_id, $role]);
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Get user data by ID
 * 
 * @param int $user_id User ID
 * @return array|false User data or false if not found
 */
function get_user($user_id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = db_query($sql, [$user_id]);
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get barber profile by user ID
 * 
 * @param int $user_id User ID
 * @return array|false Barber profile data or false if not found
 */
function get_barber_profile($user_id) {
    $sql = "SELECT * FROM barber_profiles WHERE user_id = ?";
    $stmt = db_query($sql, [$user_id]);
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get all active services
 * 
 * @return array Array of services
 */
function get_services() {
    $sql = "SELECT * FROM services WHERE status = 'active' ORDER BY category, price";
    $stmt = db_query($sql);
    
    $services = [];
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
    return $services;
}

/**
 * Get all available barbers
 * 
 * @return array Array of barbers
 */
function get_available_barbers() {
    $sql = "SELECT u.*, bp.* FROM users u 
            JOIN barber_profiles bp ON u.id = bp.user_id 
            JOIN user_roles ur ON u.id = ur.user_id 
            WHERE ur.role = 'barber' AND u.status = 'active' AND bp.is_available = 1 
            ORDER BY bp.rating DESC";
    $stmt = db_query($sql);
    
    $barbers = [];
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $barbers[] = $row;
    }
    
    return $barbers;
}

/**
 * Format price with currency
 * 
 * @param float $price Price to format
 * @return string Formatted price
 */
function format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * Generate a random string
 * 
 * @param int $length Length of string
 * @return string Random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Upload file to server
 * 
 * @param array $file File from $_FILES
 * @param string $destination Destination directory
 * @param array $allowed_types Allowed file types
 * @return string|false Filename if successful, false on failure
 */
function upload_file($file, $destination, $allowed_types = ['jpg', 'jpeg', 'png']) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check if file type is allowed
    if (!in_array($file_ext, $allowed_types)) {
        return false;
    }
    
    // Generate unique filename
    $new_filename = generate_random_string() . '.' . $file_ext;
    $upload_path = $destination . '/' . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_filename;
    }
    
    return false;
}

/**
 * Display flash message
 * 
 * @param string $message Message to display
 * @param string $type Message type (success, error, warning, info)
 * @return void
 */
function flash_message($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null if none
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    
    return null;
}

/**
 * Calculate distance between two points using Haversine formula
 * 
 * @param float $lat1 Latitude of point 1
 * @param float $lng1 Longitude of point 1
 * @param float $lat2 Latitude of point 2
 * @param float $lng2 Longitude of point 2
 * @return float Distance in kilometers
 */
function calculate_distance($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6371; // Radius of the earth in km
    
    $lat_diff = deg2rad($lat2 - $lat1);
    $lng_diff = deg2rad($lng2 - $lng1);
    
    $a = sin($lat_diff/2) * sin($lat_diff/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($lng_diff/2) * sin($lng_diff/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earth_radius * $c;
    
    return $distance;
}
?>