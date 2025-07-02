<?php
/**
 * Modern template Navbar/Header converted for PHP include.
 */
?>
<header class="section novi-background page-header">
    <div class="rd-navbar-wrap">
        <nav class="rd-navbar rd-navbar-corporate" data-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static" data-lg-stick-up="true" data-lg-stick-up-offset="118px">
            <div class="rd-navbar-aside-outer">
                <div class="rd-navbar-aside">
                    <div class="rd-navbar-panel">
                        <button class="rd-navbar-toggle" data-rd-navbar-toggle="#rd-navbar-nav-wrap-1"><span></span></button>
                        <!-- Brand -->
                        <a class="rd-navbar-brand" href="/">
                            <img src="/assets/template/images/logo-default-151x44.png" alt="" width="151" height="44" />
                        </a>
                    </div>
                </div>
            </div>
            <div class="rd-navbar-main-outer">
                <div class="rd-navbar-main">
                    <div class="rd-navbar-nav-wrap" id="rd-navbar-nav-wrap-1">
                        <ul class="rd-navbar-nav">
                            <li class="rd-nav-item"><a class="rd-nav-link" href="/">Home</a></li>
                            <li class="rd-nav-item"><a class="rd-nav-link" href="/about">About</a></li>
                            <li class="rd-nav-item"><a class="rd-nav-link" href="/contact">Contact</a></li>
                            <?php if (isset($_SESSION['user'])): ?>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="/applications">My Applications</a></li>
                            <?php endif; ?>
                        </ul>
                        <ul class="rd-navbar-nav ms-auto">
                            <?php if (isset($_SESSION['user'])): ?>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="/auth/logout">Logout</a></li>
                            <?php else: ?>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="/login">Login</a></li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="/register">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header> 