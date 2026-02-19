<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'consultant') {
    header('Location: login.php');
    exit;
}

$user_name = htmlspecialchars($_SESSION['user_name']);
$department = htmlspecialchars($_SESSION['department'] ?? '');

$stmt = $pdo->query("SELECT a.*, c.full_name as customer_name, c.iin_number as customer_iin 
                     FROM applications a 
                     JOIN customer_profiles c ON a.customer_id = c.customer_id 
                     ORDER BY a.created_at DESC");
$applications = $stmt->fetchAll();

$stmt = $pdo->query("SELECT COUNT(*) as count FROM customer_profiles");
$total_customers = $stmt->fetch()['count'];

$pending_count = 0;
$analyzed_count = 0;
foreach ($applications as $app) {
    if ($app['status'] === 'pending')
        $pending_count++;
    if ($app['status'] === 'analyzed')
        $analyzed_count++;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant Dashboard - Kenes</title>
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
                <span
                    style="font-size: 12px; background: var(--color-gray-100); padding: 2px 6px; border-radius: 4px; margin-left: 8px; color: var(--color-gray-700);">Consultant</span>
            </div>

            <button class="navbar-toggle" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <ul class="navbar-menu navbar-menu-left">
                <li><a href="#" class="active">Dashboard</a></li>
                <li><a href="#">Customers</a></li>
                <li><a href="#">Applications</a></li>
                <li><a href="#">Proposals</a></li>
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
                <h1>Overview</h1>
                <p>Track your portfolio and pending applications.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="manage_services.php" class="btn btn-secondary">Manage Services</a>
                <a href="create_case.php" class="btn btn-primary">+ New Case</a>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Active Customers</div>
                <div class="stat-value">
                    <?php echo $total_customers; ?>
                </div>
                <div class="stat-trend positive">Registered</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pending Applications</div>
                <div class="stat-value" style="color: var(--color-warning);">
                    <?php echo $pending_count; ?>
                </div>
                <div class="stat-trend">Needs Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Analyzed</div>
                <div class="stat-value">
                    <?php echo $analyzed_count; ?>
                </div>
                <div class="stat-trend positive">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total Applications</div>
                <div class="stat-value">
                    <?php echo count($applications); ?>
                </div>
                <div class="stat-trend">All Time</div>
            </div>
        </section>

        <section>
            <div class="section-header">
                <h2 class="section-title">Applications Queue</h2>
                <div style="display: flex; gap: 8px;">
                    <input type="text" placeholder="Search by customer or IIN..." class="form-control"
                        style="width: 200px; padding: 6px 12px; font-size: 14px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>App ID</th>
                            <th>Customer</th>
                            <th>IIN</th>
                            <th>Service Requested</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--color-gray-500);">
                                    No applications in the queue.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>#APP-
                                        <?php echo str_pad($app['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($app['customer_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo substr($app['customer_iin'], 0, 6) . '...'; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $app['service_type']))); ?>
                                    </td>
                                    <td>
                                        <?php echo number_format($app['amount'], 0, '.', ','); ?> KZT
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
                                        if ($app['status'] === 'sent_to_bank')
                                            $badge_class = 'badge-info';
                                        if ($app['status'] === 'bank_approved')
                                            $badge_class = 'badge-success';
                                        if ($app['status'] === 'bank_rejected')
                                            $badge_class = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('Y-m-d', strtotime($app['created_at'])); ?>
                                    </td>
                                    <td>
                                        <form action="update_status.php" method="POST"
                                            style="display: flex; gap: 4px; align-items: center;">
                                            <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                            <select name="status" class="form-control"
                                                style="padding: 4px 8px; font-size: 12px; min-width: 120px;">
                                                <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $app['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="analyzed" <?php echo $app['status'] === 'analyzed' ? 'selected' : ''; ?>>Analyzed</option>
                                                <option value="approved" <?php echo $app['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                <option value="sent_to_bank" <?php echo $app['status'] === 'sent_to_bank' ? 'selected' : ''; ?>>Send to Bank</option>
                                                <option value="bank_approved" <?php echo $app['status'] === 'bank_approved' ? 'selected' : ''; ?>>Bank Approved</option>
                                                <option value="bank_rejected" <?php echo $app['status'] === 'bank_rejected' ? 'selected' : ''; ?>>Bank Rejected</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary"
                                                style="padding: 4px 10px; font-size: 12px;">Update</button>
                                        </form>
                                        <?php if ($app['status'] === 'pending' || $app['status'] === 'processing'): ?>
                                            <form action="run_analysis.php" method="POST" style="margin-left: 4px;">
                                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit" class="btn btn-secondary"
                                                    style="padding: 4px 10px; font-size: 12px; background: #6c5ce7; color: white; border: none;">AI
                                                    Analyze</button>
                                            </form>
                                        <?php endif; ?>
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