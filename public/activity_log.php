<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

$stmt = $pdo->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 100");
$logs = $stmt->fetchAll();

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/dashboard.css"><link rel="stylesheet" href="' . $base_url . '/css/tables.css">';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Activity Log</h1>
            <p>Recent actions across the platform.</p>
        </div>
        <a href="consultant-dashboard.php" class="btn btn-ghost">← Dashboard</a>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                            <td>
                                <span class="badge <?= $log['user_type'] === 'consultant' ? 'badge-orange' : 'badge-info' ?>">
                                    <?= ucfirst($log['user_type']) ?>
                                </span>
                                #<?= $log['user_id'] ?>
                            </td>
                            <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['action']))) ?></td>
                            <td style="font-size: 13px; max-width: 400px;"><?= htmlspecialchars($log['details'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="4" style="text-align: center; padding: 32px; color: var(--text-muted);">No activity logged yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>