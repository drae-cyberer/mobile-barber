<?php
// Start output buffering to prevent header issues
ob_start();

// Any PHP logic that needs to run before HTML output should go here
// For example: session handling, redirects, etc.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Mobile Barber - On-demand Barbing Services</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Responsive Override CSS -->
    <link rel="stylesheet" href="assets/css/responsive-override.css">
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Header -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-cut me-2"></i>Mobile Barber
                </a>
                
                <!-- Mobile Search Container (Hidden by default) -->
                <div class="mobile-search-container" id="mobileSearchContainer">
                    <form action="index.php" method="get" class="d-flex">
                        <input class="form-control" type="search" name="search" placeholder="Search services..." aria-label="Search">
                        <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <div class="mobile-nav-buttons d-flex d-lg-none">
                    <button class="btn btn-outline-light me-2" id="mobileSearchToggle">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                
                <!-- Original Toggle Button (will be hidden on mobile) -->
                <button class="navbar-toggler original-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'home') ? 'active' : ''; ?>" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'services') ? 'active' : ''; ?>" href="index.php?page=services">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'barbers') ? 'active' : ''; ?>" href="index.php?page=barbers">Barbers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'booking') ? 'active' : ''; ?>" href="index.php?page=booking">Book Now</a>
                        </li>
                    </ul>
                    
                    <!-- Desktop Search Form - Removed to prevent duplicate search bars -->
                    
                    <ul class="navbar-nav">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <?php 
                                    $user = get_user($_SESSION['user_id']);
                                    echo htmlspecialchars($user['first_name']); 
                                    ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="index.php?page=profile">My Profile</a></li>
                                    <li><a class="dropdown-item" href="index.php?page=chat">Messages</a></li>
                                    <?php if (has_role('admin')): ?>
                                        <li><a class="dropdown-item" href="admin/index.php">Admin Dashboard</a></li>
                                    <?php endif; ?>
                                    <?php if (has_role('barber')): ?>
                                        <li><a class="dropdown-item" href="index.php?page=barber-dashboard">Barber Dashboard</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="users/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($page == 'login') ? 'active' : ''; ?>" href="index.php?page=login">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($page == 'register') ? 'active' : ''; ?>" href="index.php?page=register">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Flash Messages -->
    <?php $flash = get_flash_message(); ?>
    <?php if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4">