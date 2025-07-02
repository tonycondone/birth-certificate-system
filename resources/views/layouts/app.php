<?php
// Modern UI Base Layout (Bootstrap-5 template)
$SYSTEM_NAME = 'BIRTH CERTIFICATE SYSTEM';
$pageTitle = $pageTitle ?? $SYSTEM_NAME;
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="format-detection" content="telephone=no">
    <meta name="description" content="Secure digital registration, issuance and verification of birth certificates – BIRTH CERTIFICATE SYSTEM">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- CSRF Token for JS -->
    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

    <!-- Template CSS -->
    <link rel="icon" href="/assets/template/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-8tuOAd+LaN4gkQvKGItgRZtGJPD7W82dManIeZDV4SSQdlqzTeWY5AvzkdxlIo0NGdisz8Iky3Uczdlz7+eoYg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/assets/template/css/bootstrap.css">
    <link rel="stylesheet" href="/assets/template/css/fonts.css">
    <link rel="stylesheet" href="/assets/template/css/style.css" id="main-styles-link">

    <!-- Bootstrap 5.3 (overrides older template Bootstrap 4 for new components) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KKchpG5Tc5H56e9JteLoI0lp4rWuie0uVJE9idh+NROGy4tW7x1YgnPZXvBYtm0g" crossorigin="anonymous">

    <!-- Legacy custom CSS still required by system -->
    <link rel="stylesheet" href="/css/app.min.css">
</head>
<body class="site-background">
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
        
    <?php
    // TODO: Replace with componentised navbar (Phase B)
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <main class="py-4">
        <?php if (isset($content)) echo $content; ?>
    </main>

    <?php
    // TODO: Replace with componentised footer (Phase B)
    include __DIR__ . '/../partials/footer.php';
    ?>

    <!-- Template JS (includes jQuery and legacy template functionality) -->
    <script src="/assets/template/js/core.min.js"></script>
    <script src="/assets/template/js/script.js"></script>

    <!-- Bootstrap 5 bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-QYgnPZXvBYtm0gE+MnIKh4j/wcgUp3NEPoPFcAckU4iRciMghvYDn8eX2HFqRS8V" crossorigin="anonymous"></script>

    <!-- Legacy JS -->
    <script src="/js/app.min.js"></script>
</body>
</html> 