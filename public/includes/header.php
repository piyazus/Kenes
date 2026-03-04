<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/auth_guard.php';
$current_lang = $_SESSION['lang'] ?? 'en';
$is_logged_in = !empty($_SESSION['user_id']);
$user_type = $_SESSION['user_type'] ?? '';
$user_name = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Kenes — Damu Loan Consulting Platform. AI-powered loan applications for Kazakhstani businesses.">
    <title>Kenes — Damu Loan Consulting</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>/css/styles.css">
    <link rel="stylesheet" href="<?= $base_url ?>/css/navbar.css">
    <?php if (isset($extra_css))
        echo $extra_css; ?>
</head>

<body>

    <nav class="navbar">
        <div class="navbar-container">
            <!-- Brand -->
            <div class="navbar-brand">
                <a href="<?= $base_url ?>/index.php">K</a>
            </div>

            <!-- Mobile toggle -->
            <button class="navbar-toggle" id="navToggle" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <!-- Nav links -->
            <ul class="navbar-menu" id="navMenu">
                <li><a href="<?= $base_url ?>/services.php"><?= __('nav.services') ?></a></li>
                <li><a href="<?= $base_url ?>/about.php"><?= __('nav.about') ?></a></li>
                <li><a href="<?= $base_url ?>/careers.php"><?= __('nav.careers') ?></a></li>
                <?php if ($is_logged_in): ?>
                    <?php if ($user_type === 'customer'): ?>
                        <li><a href="<?= $base_url ?>/customer-dashboard.php"><?= __('nav.dashboard') ?></a></li>
                    <?php else: ?>
                        <li><a href="<?= $base_url ?>/consultant-dashboard.php"><?= __('nav.dashboard') ?></a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Right section -->
            <div class="navbar-right">
                <!-- Language switcher -->
                <?php
                $current_params = $_GET;
                $current_params['lang'] = 'en';
                $en_url = '?' . http_build_query($current_params);
                $current_params['lang'] = 'kz';
                $kz_url = '?' . http_build_query($current_params);
                $current_params['lang'] = 'ru';
                $ru_url = '?' . http_build_query($current_params);
                ?>
                <div class="lang-switcher">
                    <a href="<?= htmlspecialchars($en_url) ?>"
                        class="<?= $current_lang === 'en' ? 'active-lang' : '' ?>">EN</a>
                    <a href="<?= htmlspecialchars($kz_url) ?>"
                        class="<?= $current_lang === 'kz' ? 'active-lang' : '' ?>">KZ</a>
                    <a href="<?= htmlspecialchars($ru_url) ?>"
                        class="<?= $current_lang === 'ru' ? 'active-lang' : '' ?>">RU</a>
                </div>

                <?php if ($is_logged_in): ?>
                    <span class="navbar-user-name hidden-mobile"><?= htmlspecialchars($user_name) ?></span>
                    <a href="<?= $base_url ?>/logout.php" class="btn btn-ghost btn-sm"><?= __('nav.logout') ?></a>
                <?php else: ?>
                    <a href="<?= $base_url ?>/login.php" class="btn btn-ghost btn-sm"><?= __('nav.login') ?></a>
                    <a href="<?= $base_url ?>/register-customer.php"
                        class="btn btn-primary btn-sm"><?= __('nav.apply') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>