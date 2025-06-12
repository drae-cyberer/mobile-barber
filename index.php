<?php
// Start session for user authentication
session_start();

// Include configuration files
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if search parameter exists and redirect to search page
if (isset($_GET['search']) && !empty($_GET['search'])) {
    // If page is not already set to search, redirect to search page
    if (!isset($_GET['page']) || $_GET['page'] !== 'search') {
        $search_query = urlencode($_GET['search']);
        header("Location: index.php?page=search&search={$search_query}");
        exit;
    }
}

// Get current page from URL parameter or default to home
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Include header
include_once 'includes/header.php';

// Load appropriate page content based on URL parameter
switch ($page) {
    case 'home':
        include_once 'includes/pages/home.php';
        break;
    case 'services':
        include_once 'includes/pages/services.php';
        break;
    case 'barbers':
        include_once 'includes/pages/barbers.php';
        break;
    case 'booking':
        include_once 'includes/pages/booking.php';
        break;
    case 'chat':
        // Check if user is logged in before accessing chat
        if (isset($_SESSION['user_id'])) {
            include_once 'chat/index.php';
        } else {
            // Redirect to login if not authenticated
            header('Location: index.php?page=login&redirect=chat');
            exit;
        }
        break;
    case 'login':
        include_once 'users/login.php';
        break;
    case 'register':
        include_once 'users/register.php';
        break;
    case 'profile':
        // Check if user is logged in before accessing profile
        if (isset($_SESSION['user_id'])) {
            include_once 'users/profile.php';
        } else {
            // Redirect to login if not authenticated
            header('Location: index.php?page=login&redirect=profile');
            exit;
        }
        break;
    case 'payment':
        // Check if user is logged in before accessing payment
        if (isset($_SESSION['user_id'])) {
            include_once 'payment/checkout.php';
        } else {
            // Redirect to login if not authenticated
            header('Location: index.php?page=login&redirect=payment');
            exit;
        }
        break;
    case 'search':
        // Handle search functionality
        include_once 'includes/pages/search.php';
        break;
    default:
        // 404 page not found
        include_once 'includes/pages/404.php';
        break;
}

// Include footer
// Include footer (check if it should be hidden)
if (!isset($hide_footer) || !$hide_footer) {
    include_once 'includes/footer.php';
}
?>