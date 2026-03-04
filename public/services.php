<?php
require_once 'includes/db.php';

// Fetch active services
$stmt = $pdo->query("SELECT * FROM services WHERE active = 1 ORDER BY id");
$services = $stmt->fetchAll();

// Get unique loan types for filter
$types = array_unique(array_column($services, 'loan_type'));

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/tables.css">';
include 'includes/header.php';
?>

<main class="container" style="padding-top: 48px; padding-bottom: 80px;">
    <header style="margin-bottom: 40px;">
        <h1><?= __('services.title') ?></h1>
        <p style="font-size: 16px;"><?= __('services.consult') ?></p>
    </header>

    <!-- Filter tabs -->
    <div class="filter-tabs" id="serviceFilters">
        <button class="filter-tab active" data-filter="all"><?= __('services.filter_all') ?></button>
        <?php foreach ($types as $type): ?>
            <button class="filter-tab"
                data-filter="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $type))) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Services grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;"
        id="servicesGrid">
        <?php foreach ($services as $svc): ?>
            <div class="card service-item" data-type="<?= htmlspecialchars($svc['loan_type']) ?>">
                <h3 style="font-size: 1.2rem; margin-bottom: 8px; color: var(--yc-orange);">
                    <?= htmlspecialchars($svc['name']) ?>
                </h3>

                <?php if ($svc['interest_rate']): ?>
                    <span class="badge badge-orange" style="margin-bottom: 12px;">from <?= $svc['interest_rate'] ?>% APR</span>
                <?php endif; ?>

                <p style="font-size: 14px; margin-bottom: 16px; line-height: 1.5;">
                    <?= htmlspecialchars($svc['description']) ?>
                </p>

                <ul
                    style="list-style: none; padding: 0; margin: 0 0 16px; display: flex; gap: 16px; flex-wrap: wrap; font-size: 13px; color: var(--text-secondary);">
                    <?php if ($svc['max_amount']): ?>
                        <li><strong>Max:</strong> <?= number_format($svc['max_amount'], 0, '.', ',') ?> KZT</li>
                    <?php endif; ?>
                    <?php if ($svc['duration']): ?>
                        <li><strong>Term:</strong> <?= htmlspecialchars($svc['duration']) ?></li>
                    <?php endif; ?>
                </ul>

                <a href="register-customer.php" class="btn btn-primary btn-sm"><?= __('services.apply') ?></a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($services)): ?>
        <div class="empty-state">
            <p>No services available at this time.</p>
        </div>
    <?php endif; ?>

    <!-- CTA -->
    <div
        style="border-top: 1px solid var(--border); padding-top: 40px; margin-top: 48px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h3 style="margin-bottom: 4px;"><?= __('services.not_sure') ?></h3>
            <p style="margin: 0; font-size: 14px;"><?= __('services.consult') ?></p>
        </div>
        <a href="register-customer.php" class="btn btn-primary"><?= __('services.book') ?></a>
    </div>
</main>

<script>
    // Service filter
    document.querySelectorAll('.filter-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.dataset.filter;
            document.querySelectorAll('.service-item').forEach(function (item) {
                if (filter === 'all' || item.dataset.type === filter) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>