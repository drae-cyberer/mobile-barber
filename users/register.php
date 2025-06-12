<?php
$hide_footer = true;
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files for functions and DB connection (adjust paths as needed)
require_once(dirname(__DIR__) . '/config/database.php'); // or wherever your db_query, $conn, etc. are defined
require_once(dirname(__DIR__) . '/includes/functions.php'); // for sanitize_input, hash_password, flash_message, etc.

// Initialize variables
$errors = [];
$username = $email = $first_name = $last_name = $phone = $address = $user_type = "";

// Handle form submission before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $user_type = sanitize_input($_POST['user_type']);

    // Validate input
    if (empty($username)) {
        $errors[] = "Username is required";
    } else {
        // Check if username already exists
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = db_query($sql, [$username]);
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Username already taken";
        }
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = db_query($sql, [$email]);
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($first_name)) {
        $errors[] = "First name is required";
    }

    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }

    if (!in_array($user_type, ['client', 'barber'])) {
        $errors[] = "Invalid user type";
    }

    // If no errors, register user
    if (empty($errors)) {
        // Hash password
        $hashed_password = hash_password($password);

        // Begin transaction
        global $conn;
        $conn->begin_transaction();

        try {
            // Insert user
            $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = db_query($sql, [$username, $email, $hashed_password, $first_name, $last_name, $phone, $address]);

            // Get user ID
            $user_id = $conn->insert_id;

            // Insert user role
            $sql = "INSERT INTO user_roles (user_id, role) VALUES (?, ?)";
            $stmt = db_query($sql, [$user_id, $user_type]);

            // If user is a barber, create barber profile
            if ($user_type === 'barber') {
                $sql = "INSERT INTO barber_profiles (user_id, hourly_rate) VALUES (?, ?)";
                $stmt = db_query($sql, [$user_id, 20.00]); // Default hourly rate
            }

            // Commit transaction
            $conn->commit();

            // Set success message
            flash_message("Registration successful! You can now login.", "success");

            // Redirect to login page
            header("Location: index.php?page=login");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}


?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-md">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Create an Account</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                                <div class="invalid-feedback">Please choose a username.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">Please enter a password.</div>
                                <div class="mt-2">
                                    <div class="progress">
                                        <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div class="invalid-feedback">Please confirm your password.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                                <div class="invalid-feedback">Please enter your phone number.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_type" class="form-label">I am a</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="" selected disabled>Select user type</option>
                                    <option value="client" <?php echo (isset($user_type) && $user_type === 'client') ? 'selected' : ''; ?>>Client</option>
                                    <option value="barber" <?php echo (isset($user_type) && $user_type === 'barber') ? 'selected' : ''; ?>>Barber</option>
                                </select>
                                <div class="invalid-feedback">Please select a user type.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                            <div class="invalid-feedback">You must agree before submitting.</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Already have an account? <a href="index.php?page=login">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>