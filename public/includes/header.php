<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenes</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/navbar.css">
    <?php if (isset($extra_css))
        echo $extra_css; ?>
</head>

<body>

    <nav class="navbar">
        <div class="navbar-container">

            <div class="navbar-brand">
                <a href="index.php">K</a>
            </div>

            <button class="navbar-toggle" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>


            <ul class="navbar-menu navbar-menu-left">
                <li><a href="about.php">About</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="algorithms.php">Algorithms</a></li>
            </ul>


            <ul class="navbar-menu">
                <li><a href="login.php">Login</a></li>
                <li><a href="register-customer.php" class="btn btn-primary" style="color: white;">Apply</a></li>
            </ul>
        </div>
    </nav>