<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user if logged in
$auth = new \App\Auth\Authentication();
$currentUser = $auth->getCurrentUser();
$userRole = $currentUser ? $currentUser['role'] : null;

// Define page-specific meta information
$pageTitle = $pageTitle ?? 'Digital Birth Certificate System';
$pageDescription = $pageDescription ?? 'Secure and efficient digital birth certificate management system for parents, hospitals, and government registrars.';
$pageUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Primary Meta Tags -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($pageUrl); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:image" content="/images/og-image.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo htmlspecialchars($pageUrl); ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="twitter:image" content="/images/og-image.jpg">
    
    <!-- Favicon -->
    <link rel="icon" href="/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
    
    <!-- Preload Fonts -->
    <!-- Note: Removed preload for Inter font as it's not currently used and was causing 404 errors. 
         To re-enable, ensure the font files are in the correct /public/fonts directory and uncomment the line below.
    -->
    <!-- <link rel="preload" href="/fonts/inter.woff2" as="font" type="font/woff2" crossorigin> -->

    <!-- Template CSS -->
    <link rel="stylesheet" href="/assets/template/css/bootstrap.css">
    
    <!-- Schema.org Markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "GovernmentService",
        "name": "Digital Birth Certificate System",
        "description": "<?php echo htmlspecialchars($pageDescription); ?>",
        "provider": {
            "@type": "GovernmentOrganization",
            "name": "Birth Registry Department"
        },
        "areaServed": "Country",
        "audience": {
            "@type": "Audience",
            "audienceType": "Citizens"
        }
    }
    </script>
</head>
<body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Birth Certificate System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
<header class="bg-white shadow-sm" role="banner">
    <nav class="container mx-auto px-4 py-3" role="navigation" aria-label="Main navigation">
        <div class="flex justify-between items-center">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <a href="/" class="text-xl font-bold text-primary" aria-label="Home">
                    Digital Birth Certificate System
                </a>
            </div>

            <!-- Main Navigation -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="/" class="nav-link">Home</a>
                <a href="/about" class="nav-link">About</a>
                <a href="/verify" class="nav-link">Verify Certificate</a>
                <a href="/contact" class="nav-link">Contact</a>
                <a href="/faq" class="nav-link">FAQ</a>

                <?php if ($userRole): ?>
                    <!-- Role-specific Navigation -->
                    <?php if ($userRole === 'parent'): ?>
                        <a href="/dashboard/parent" class="nav-link">Dashboard</a>
                        <a href="/applications/parent-form" class="nav-link">New Application</a>
                    <?php elseif ($userRole === 'hospital'): ?>
                        <a href="/dashboard/hospital" class="nav-link">Dashboard</a>
                        <a href="/applications/hospital-form" class="nav-link">Verify Birth</a>
                    <?php elseif ($userRole === 'registrar'): ?>
                        <a href="/dashboard" class="nav-link">Dashboard</a>
                    <?php elseif ($userRole === 'admin'): ?>
                        <a href="/dashboard/admin" class="nav-link">Admin Dashboard</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <?php if ($currentUser): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                            <span><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                            <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="/notifications" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Notifications</a>
                            <form action="/auth/logout" method="POST" class="block">
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/auth/login" class="btn-secondary">Login</a>
                    <a href="/auth/register" class="btn-primary">Register</a>
                <?php endif; ?>
            </div>

                <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button @click="mobileMenu = !mobileMenu" 
                        class="text-gray-500 hover:text-gray-900"
                        aria-expanded="false"
                        aria-controls="mobile-menu"
                        aria-label="Toggle navigation menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="mobileMenu" 
             class="md:hidden mt-4" 
             id="mobile-menu" 
             role="menu" 
             aria-labelledby="mobile-menu-button">
            <a href="/" class="block py-2 text-gray-700 hover:text-gray-900">Home</a>
            <a href="/about" class="block py-2 text-gray-700 hover:text-gray-900">About</a>
            <a href="/verify" class="block py-2 text-gray-700 hover:text-gray-900">Verify Certificate</a>
            <a href="/contact" class="block py-2 text-gray-700 hover:text-gray-900">Contact</a>
            <a href="/faq" class="block py-2 text-gray-700 hover:text-gray-900">FAQ</a>

            <?php if ($userRole): ?>
                <?php if ($userRole === 'parent'): ?>
                    <a href="/dashboard/parent" class="block py-2 text-gray-700 hover:text-gray-900">Dashboard</a>
                    <a href="/applications/parent-form" class="block py-2 text-gray-700 hover:text-gray-900">New Application</a>
                <?php elseif ($userRole === 'hospital'): ?>
                    <a href="/dashboard/hospital" class="block py-2 text-gray-700 hover:text-gray-900">Dashboard</a>
                    <a href="/applications/hospital-form" class="block py-2 text-gray-700 hover:text-gray-900">Verify Birth</a>
                <?php elseif ($userRole === 'registrar'): ?>
                    <a href="/dashboard" class="block py-2 text-gray-700 hover:text-gray-900">Dashboard</a>
                <?php elseif ($userRole === 'admin'): ?>
                    <a href="/dashboard/admin" class="block py-2 text-gray-700 hover:text-gray-900">Admin Dashboard</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </nav>
</header>

<style>
.nav-link {
    @apply text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium;
}

.btn-primary {
    @apply bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150;
}

.btn-secondary {
    @apply bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition duration-150;
}
</style>