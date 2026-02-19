<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$user_name = htmlspecialchars($_SESSION['user_name']);
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM applications WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

$total_applications = count($applications);
$pending_action = 0;
$proposals_received = 0;

foreach ($applications as $app) {
    if ($app['status'] === 'pending')
        $pending_action++;
    if ($app['status'] === 'analyzed')
        $proposals_received++;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Kenes</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/tables.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="index.html">K</a>
            </div>

            <button class="navbar-toggle" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <ul class="navbar-menu navbar-menu-left">
                <li><a href="#" class="active">Dashboard</a></li>
                <li><a href="#">My Applications</a></li>
                <li><a href="#">Documents</a></li>
                <li><a href="#">Profile</a></li>
            </ul>

            <div class="navbar-user">
                <span style="font-weight: 600; color: var(--color-gray-900);">
                    <?php echo $user_name; ?>
                </span>
                <a href="logout.php" class="btn btn-text" style="font-size: 14px; margin-left: 12px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">

        <header class="dashboard-header">
            <div class="dashboard-welcome">
                <h1>Dashboard</h1>
                <p>Welcome back,
                    <?php echo $user_name; ?>. Here is your lending overview.
                </p>
            </div>
            <div>
                <a href="application.html" class="btn btn-primary">+ New Application</a>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">My Applications</div>
                <div class="stat-value">
                    <?php echo $total_applications; ?>
                </div>
                <div class="stat-trend">Total Submitted</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Proposals Received</div>
                <div class="stat-value" style="color: var(--color-success);">
                    <?php echo $proposals_received; ?>
                </div>
                <div class="stat-trend positive">Ready for review</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pending Action</div>
                <div class="stat-value" style="color: var(--color-warning);">
                    <?php echo $pending_action; ?>
                </div>
                <div class="stat-trend">Awaiting Review</div>
            </div>
        </section>

        <section>
            <div class="section-header">
                <h2 class="section-title">My Applications</h2>
                <a href="#" class="btn btn-text" style="font-size: 14px;">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Service Requested</th>
                            <th>Amount</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--color-gray-500);">
                                    No applications yet. <a href="application.html">Submit your first application</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>#APP-
                                        <?php echo str_pad($app['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $app['service_type']))); ?>
                                    </td>
                                    <td>
                                        <?php echo number_format($app['amount'], 0, '.', ','); ?> KZT
                                    </td>
                                    <td>
                                        <?php echo date('Y-m-d', strtotime($app['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = 'badge-secondary';
                                        if ($app['status'] === 'pending')
                                            $badge_class = 'badge-warning';
                                        if ($app['status'] === 'processing')
                                            $badge_class = 'badge-info';
                                        if ($app['status'] === 'analyzed')
                                            $badge_class = 'badge-success';
                                        if ($app['status'] === 'approved')
                                            $badge_class = 'badge-success';
                                        if ($app['status'] === 'rejected')
                                            $badge_class = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <script src="js/navbar.js"></script>
</body>

</html>