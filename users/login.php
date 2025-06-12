<?php
$hide_footer = true;
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = db_query($sql, [$email]);
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verify_password($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Get user roles
                $sql = "SELECT role FROM user_roles WHERE user_id = ?";
                $stmt = db_query($sql, [$user['id']]);
                $roles_result = $stmt->get_result();
                
                $roles = [];
                while ($role = $roles_result->fetch_assoc()) {
                    $roles[] = $role['role'];
                }
                
                $_SESSION['roles'] = $roles;
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = generate_random_string(32);
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $sql = "INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                    db_query($sql, [$user['id'], $token, date('Y-m-d H:i:s', $expiry)]);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expiry, '/', '', false, true);
                }
                
                // Redirect to appropriate page
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'home';
                flash_message("Welcome back, {$user['first_name']}!", "success");
                header("Location: index.php?page={$redirect}");
                exit;
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-md">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Login</h2>
                    
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
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="index.php?page=register">Register</a></p>
                        <p><a href="#">Forgot your password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>