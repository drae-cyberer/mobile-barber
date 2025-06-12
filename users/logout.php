<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    // Delete token from database
    require_once '../config/database.php';
    $token = $_COOKIE['remember_token'];
    $sql = "DELETE FROM user_tokens WHERE token = ?";
    db_query($sql, [$token]);
    
    // Delete cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Redirect to home page with success message
header("Location: ../index.php?page=home&msg=logout_success");
exit;