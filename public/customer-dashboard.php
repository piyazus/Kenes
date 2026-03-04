<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
requireAuth('customer');

$user_name = htmlspecialchars($_SESSION['user_name']);
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? '';

// Fetch applications
$stmt = $pdo->prepare("SELECT a.*, s.name as service_name FROM applications a LEFT JOIN services s ON a.service_id = s.id WHERE a.customer_id = ? ORDER BY a.created_at DESC");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

$total_applications = count($applications);
$pending_action = 0;
$proposals_received = 0;
foreach ($applications as $app) {
    if (in_array($app['status'], ['submitted', 'pending']))
        $pending_action++;
    if (in_array($app['status'], ['analyzed', 'approved', 'bank_approved']))
        $proposals_received++;
}

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/dashboard.css"><link rel="stylesheet" href="' . $base_url . '/css/tables.css">';
include 'includes/header.php';
?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
            <div class="sidebar-name"><?= $user_name ?></div>
            <div class="sidebar-email"><?= htmlspecialchars($user_email) ?></div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="customer-dashboard.php" class="active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                    </svg>
                    <?= __('dash.my_apps') ?>
                </a></li>
            <li><a href="application.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <?= __('dash.new_app') ?>
                </a></li>
            <li><a href="profile.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <?= __('dash.my_profile') ?>
                </a></li>
            <li><a href="logout.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    <?= __('nav.logout') ?>
                </a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1><?= __('dash.welcome') ?>, <?= $user_name ?></h1>
                <p><?= __('dash.overview') ?></p>
            </div>
            <a href="application.php" class="btn btn-primary"><?= __('dash.new_app') ?></a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title"><?= __('dash.total_apps') ?></div>
                <div class="stat-value"><?= $total_applications ?></div>
                <div class="stat-trend"><?= __('dash.submitted') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><?= __('dash.proposals') ?></div>
                <div class="stat-value" style="color: var(--color-success);"><?= $proposals_received ?></div>
                <div class="stat-trend positive"><?= __('dash.ready') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><?= __('dash.pending') ?></div>
                <div class="stat-value" style="color: var(--color-warning);"><?= $pending_action ?></div>
                <div class="stat-trend"><?= __('dash.awaiting') ?></div>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Applications table -->
        <div class="section-header">
            <h2 class="section-title"><?= __('dash.my_apps') ?></h2>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                    <?= __('dash.no_apps') ?> <a href="application.php"><?= __('dash.first_app') ?></a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>#APP-<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($app['service_name'] ?? ucwords(str_replace('_', ' ', $app['service_type'] ?? 'N/A'))) ?>
                                    </td>
                                    <td><?= number_format($app['amount'], 0, '.', ',') ?> KZT</td>
                                    <td><?= date('M d, Y', strtotime($app['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        $bc = 'badge-secondary';
                                        if (in_array($app['status'], ['submitted', 'pending']))
                                            $bc = 'badge-warning';
                                        if ($app['status'] === 'processing' || $app['status'] === 'under_review')
                                            $bc = 'badge-info';
                                        if (in_array($app['status'], ['analyzed', 'approved', 'bank_approved']))
                                            $bc = 'badge-success';
                                        if (in_array($app['status'], ['rejected', 'bank_rejected']))
                                            $bc = 'badge-danger';
                                        if ($app['status'] === 'sent_to_bank')
                                            $bc = 'badge-info';
                                        ?>
                                        <span
                                            class="badge <?= $bc ?>"><?= ucfirst(str_replace('_', ' ', $app['status'])) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Document Checklist -->
        <div style="margin-top: 32px;">
            <div class="section-header">
                <h2 class="section-title"><?= __('dash.doc_checklist') ?></h2>
            </div>
            <div class="card">
                <div class="checklist-item"><input type="checkbox"> <?= __('doc.business_plan') ?></div>
                <div class="checklist-item"><input type="checkbox"> <?= __('doc.financial') ?></div>
                <div class="checklist-item"><input type="checkbox"> <?= __('doc.iin_cert') ?></div>
                <div class="checklist-item"><input type="checkbox"> <?= __('doc.bank_statement') ?></div>
                <div class="checklist-item"><input type="checkbox"> Company registration certificate</div>
                <div class="checklist-item"><input type="checkbox"> Tax declarations (last 2 years)</div>
            </div>
        </div>
    </main>
</div>

<script src="<?= $base_url ?>/js/navbar.js"></script>

<?php include 'includes/footer.php'; ?>