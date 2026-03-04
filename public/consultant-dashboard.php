<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

$user_name = htmlspecialchars($_SESSION['user_name']);
$department = htmlspecialchars($_SESSION['department'] ?? '');

// Fetch applications with pagination (50 per page)
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("SELECT a.*, c.full_name as customer_name, c.email as customer_email, c.phone as customer_phone, c.iin_bin, c.city_region, c.business_name,
                      s.name as service_name
                      FROM applications a 
                      JOIN customers c ON a.customer_id = c.id 
                      LEFT JOIN services s ON a.service_id = s.id
                      ORDER BY a.created_at DESC
                      LIMIT " . intval($per_page) . " OFFSET " . intval($offset));
$stmt->execute();
$applications = $stmt->fetchAll();

// Stats
$total_customers_stmt = $pdo->query("SELECT COUNT(*) as cnt FROM customers");
$total_customers = $total_customers_stmt->fetch()['cnt'];

$pending_count = 0;
$analyzed_count = 0;
foreach ($applications as $app) {
    if (in_array($app['status'], ['submitted', 'pending']))
        $pending_count++;
    if ($app['status'] === 'analyzed')
        $analyzed_count++;
}
?>
<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant Dashboard - Kenes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>/css/styles.css">
    <link rel="stylesheet" href="<?= $base_url ?>/css/navbar.css">
    <link rel="stylesheet" href="<?= $base_url ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= $base_url ?>/css/tables.css">
</head>

<body>

    <!-- Top navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="<?= $base_url ?>/index.php">K</a>
                <span class="navbar-badge">Consultant</span>
            </div>
            <ul class="navbar-menu" id="navMenu">
                <li><a href="consultant-dashboard.php" class="active"><?= __('cdash.overview') ?></a></li>
                <li><a href="manage_services.php"><?= __('cdash.manage_services') ?></a></li>
                <li><a href="activity_log.php">Activity Log</a></li>
            </ul>
            <div class="navbar-right">
                <div class="lang-switcher">
                    <a href="?lang=en" class="<?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'active-lang' : '' ?>">EN</a>
                    <a href="?lang=kz" class="<?= ($_SESSION['lang'] ?? 'en') === 'kz' ? 'active-lang' : '' ?>">KZ</a>
                    <a href="?lang=ru" class="<?= ($_SESSION['lang'] ?? 'en') === 'ru' ? 'active-lang' : '' ?>">RU</a>
                </div>
                <span class="navbar-user-name"><?= $user_name ?></span>
                <a href="logout.php" class="btn btn-ghost btn-sm"><?= __('nav.logout') ?></a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Header with stats -->
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div>
                <h1><?= __('cdash.overview') ?></h1>
                <p><?= __('cdash.portfolio') ?></p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="manage_services.php" class="btn btn-secondary"><?= __('cdash.manage_services') ?></a>
                <a href="create_case.php" class="btn btn-primary"><?= __('cdash.new_case') ?></a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title"><?= __('cdash.active_customers') ?></div>
                <div class="stat-value"><?= $total_customers ?></div>
                <div class="stat-trend positive">Registered</div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><?= __('cdash.pending_apps') ?></div>
                <div class="stat-value" style="color: var(--color-warning);"><?= $pending_count ?></div>
                <div class="stat-trend">Needs Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><?= __('cdash.analyzed') ?></div>
                <div class="stat-value"><?= $analyzed_count ?></div>
                <div class="stat-trend positive">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><?= __('cdash.total_apps') ?></div>
                <div class="stat-value"><?= count($applications) ?></div>
                <div class="stat-trend">All Time</div>
            </div>
        </div>
    </div>

    <!-- 3-Column Layout -->
    <div class="three-col-layout">
        <!-- Column 1: Case Queue -->
        <div class="col-panel" id="caseQueue">
            <div class="col-panel-header">
                <h2><?= __('cdash.queue') ?></h2>
                <div style="margin-top: 8px;">
                    <div class="filter-tabs" id="statusFilters">
                        <button class="filter-tab active" data-filter="all"><?= __('cdash.filter_all') ?></button>
                        <button class="filter-tab" data-filter="submitted">Pending</button>
                        <button class="filter-tab" data-filter="analyzed">Analyzed</button>
                        <button class="filter-tab" data-filter="sent_to_bank">Sent</button>
                    </div>
                    <div class="search-bar" style="margin-top: 8px; margin-bottom: 0;">
                        <input type="text" id="caseSearch" placeholder="<?= __('misc.search') ?>"
                            style="font-size: 13px;">
                    </div>
                </div>
            </div>

            <?php foreach ($applications as $i => $app): ?>
                <div class="case-item <?= $i === 0 ? 'active' : '' ?>" data-id="<?= $app['id'] ?>"
                    data-status="<?= $app['status'] ?>"
                    data-name="<?= htmlspecialchars(strtolower($app['customer_name'])) ?>"
                    data-app="<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>"
                    onclick="selectCase(this, JSON.parse(this.dataset.app))">
                    <div class="case-item-name"><?= htmlspecialchars($app['customer_name']) ?></div>
                    <div class="case-item-meta">
                        <span><?= htmlspecialchars($app['service_name'] ?? ucwords(str_replace('_', ' ', $app['service_type'] ?? ''))) ?></span>
                        <span>·</span>
                        <span><?= date('M d', strtotime($app['created_at'])) ?></span>
                        <span>·</span>
                        <?php
                        $bc = 'badge-secondary';
                        if (in_array($app['status'], ['submitted', 'pending']))
                            $bc = 'badge-warning';
                        if ($app['status'] === 'analyzed')
                            $bc = 'badge-success';
                        if ($app['status'] === 'sent_to_bank')
                            $bc = 'badge-info';
                        if (in_array($app['status'], ['approved', 'bank_approved']))
                            $bc = 'badge-success';
                        if (in_array($app['status'], ['rejected', 'bank_rejected']))
                            $bc = 'badge-danger';
                        ?>
                        <span class="badge <?= $bc ?>"
                            style="font-size: 10px;"><?= ucfirst(str_replace('_', ' ', $app['status'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <p>No applications in the queue.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Column 2: Case Detail -->
        <div class="col-panel" id="caseDetail" style="background: var(--bg-body);">
            <div class="col-panel-header">
                <h2><?= __('cdash.case_detail') ?></h2>
            </div>
            <div class="col-panel-body" id="detailContent">
                <div class="empty-state">
                    <p>Select a case from the queue</p>
                </div>
            </div>
        </div>

        <!-- Column 3: AI Analysis Panel -->
        <div class="col-panel" id="aiPanel">
            <div class="col-panel-header">
                <h2><?= __('cdash.ai_panel') ?></h2>
            </div>
            <div class="col-panel-body" id="aiContent">
                <div class="empty-state">
                    <p>Select a case to view AI analysis</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        var currentAppId = null;

        function selectCase(el, app) {
            // Highlight active
            document.querySelectorAll('.case-item').forEach(function (item) { item.classList.remove('active'); });
            el.classList.add('active');
            currentAppId = app.id;

            // Populate Column 2 — Case Detail
            var detailHtml = '<div class="card" style="margin-bottom: 16px;">' +
                '<h4 style="margin-bottom: 12px;">Customer Profile</h4>' +
                '<div style="font-size: 14px; display: grid; gap: 8px;">' +
                '<div><strong>Name:</strong> ' + escHtml(app.customer_name) + '</div>' +
                '<div><strong>Email:</strong> ' + escHtml(app.customer_email || '') + '</div>' +
                '<div><strong>Phone:</strong> ' + escHtml(app.customer_phone || '') + '</div>' +
                '<div><strong>IIN/BIN:</strong> ' + escHtml(app.iin_bin || '') + '</div>' +
                '<div><strong>City:</strong> ' + escHtml(app.city_region || '') + '</div>' +
                '<div><strong>Business:</strong> ' + escHtml(app.business_name || '') + '</div>' +
                '</div></div>' +
                '<div class="card" style="margin-bottom: 16px;">' +
                '<h4>Application Details</h4>' +
                '<div style="font-size: 14px; display: grid; gap: 8px;">' +
                '<div><strong>Service:</strong> ' + escHtml(app.service_name || app.service_type || '') + '</div>' +
                '<div><strong>Amount:</strong> ' + Number(app.amount).toLocaleString() + ' KZT</div>' +
                '<div><strong>Status:</strong> <select id="statusSelect" class="form-control" style="display: inline; width: auto; padding: 4px 8px; font-size: 13px;" onchange="updateStatus(' + app.id + ', this.value)">' +
                statusOptions(app.status) +
                '</select></div>' +
                '</div></div>' +
                '<div class="card" style="margin-bottom: 16px;">' +
                '<h4>Consultant Notes</h4>' +
                '<textarea class="form-control" id="consultantNotes" rows="4" placeholder="Add notes about this case..." oninput="autoSaveNotes(' + app.id + ')">' + escHtml(app.notes || '') + '</textarea>' +
                '</div>' +
                '<div style="display: flex; gap: 8px; flex-wrap: wrap;">' +
                '<button class="btn btn-primary btn-sm" onclick="runAI(' + app.id + ')">' + <?= json_encode(__('cdash.run_ai')) ?> + '</button>' +
                '<button class="btn btn-secondary btn-sm" onclick="updateStatus(' + app.id + ', \'sent_to_bank\')">' + <?= json_encode(__('cdash.send_bank')) ?> + '</button>' +
                '<a href="export_report.php?app_id=' + app.id + '" class="btn btn-ghost btn-sm">' + <?= json_encode(__('cdash.export')) ?> + '</a>' +
                '</div>';

            document.getElementById('detailContent').innerHTML = detailHtml;

            // Load AI analysis for Column 3
            loadAIPanel(app.id);
        }

        function statusOptions(current) {
            var statuses = ['submitted', 'pending', 'processing', 'under_review', 'analyzed', 'sent_to_bank', 'approved', 'rejected', 'bank_approved', 'bank_rejected'];
            return statuses.map(function (s) {
                return '<option value="' + s + '"' + (s === current ? ' selected' : '') + '>' + s.replace(/_/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); }) + '</option>';
            }).join('');
        }

        function escHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Update status via AJAX
        function updateStatus(appId, status) {
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'app_id=' + appId + '&status=' + status
            }).then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        // Auto-save notes (debounced)
        var saveTimer;
        function autoSaveNotes(appId) {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function () {
                var notes = document.getElementById('consultantNotes').value;
                fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'app_id=' + appId + '&notes=' + encodeURIComponent(notes) + '&action=save_notes'
                });
            }, 500);
        }

        // Run AI Analysis
        function runAI(appId) {
            document.getElementById('aiContent').innerHTML = '<div class="text-center" style="padding: 32px;"><p>Running AI analysis...</p><div style="width: 40px; height: 40px; border: 3px solid var(--border); border-top-color: var(--yc-orange); border-radius: 50%; animation: spin 1s linear infinite; margin: 16px auto;"></div></div>';
            fetch('run_analysis.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'app_id=' + appId
            }).then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        loadAIPanel(appId);
                    } else {
                        document.getElementById('aiContent').innerHTML = '<div class="alert alert-error">' + (data.error || 'Analysis failed') + '</div>';
                    }
                }).catch(function () {
                    document.getElementById('aiContent').innerHTML = '<div class="alert alert-error">Failed to connect to AI service</div>';
                });
        }

        // Load AI Panel
        function loadAIPanel(appId) {
            fetch('run_analysis.php?app_id=' + appId)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.proposal) {
                        var p = data.proposal;
                        var html = '<div class="card" style="margin-bottom: 12px;">' +
                            '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 14px;">' +
                            '<div><strong>Scoring:</strong><br><span style="font-size: 24px; font-weight: 700; color: var(--yc-orange);">' + (p.scoring_points || '—') + '</span></div>' +
                            '<div><strong>Risk Level:</strong><br><span class="badge ' + riskBadge(p.risk_level) + '">' + escHtml(p.risk_level || '—') + '</span></div>' +
                            '</div></div>' +
                            '<div class="card" style="margin-bottom: 12px;">' +
                            '<h4>Recommended Amount</h4>' +
                            '<p style="font-size: 20px; font-weight: 700;">' + (p.recommended_amount ? Number(p.recommended_amount).toLocaleString() + ' KZT' : '—') + '</p>' +
                            '</div>' +
                            '<div class="card" style="margin-bottom: 12px;">' +
                            '<h4>AI Summary</h4>' +
                            '<p style="font-size: 13px; line-height: 1.6;">' + escHtml(p.ai_summary || 'No summary available') + '</p>' +
                            '</div>' +
                            '<div style="display: flex; gap: 8px; flex-wrap: wrap;">' +
                            '<button class="btn btn-primary btn-sm" onclick="runAI(' + appId + ')">Regenerate</button>' +
                            '<button class="btn btn-secondary btn-sm" onclick="generateProposal(' + appId + ', \'text\')">Generate Proposal (Claude)</button>' +
                            '<button class="btn btn-secondary btn-sm" onclick="generateProposal(' + appId + ', \'docx\')">' + <?= json_encode(__('cdash.generate_word')) ?> + '</button>' +
                            '<button class="btn btn-ghost btn-sm" onclick="generateProposal(' + appId + ', \'pptx\')">' + <?= json_encode(__('cdash.generate_pptx')) ?> + '</button>' +
                            '<a href="export_report.php?app_id=' + appId + '" class="btn btn-ghost btn-sm" style="font-size:11px;">View Report</a>' +
                            '</div>' +
                            '<div id="proposalOutput" style="margin-top: 12px;"></div>';
                        document.getElementById('aiContent').innerHTML = html;
                    } else {
                        document.getElementById('aiContent').innerHTML = '<div class="empty-state"><p>No analysis yet. Click "Run AI Analysis" to begin.</p></div>';
                    }
                }).catch(function () {
                    document.getElementById('aiContent').innerHTML = '<div class="empty-state"><p>Select a case and run analysis</p></div>';
                });
        }

        function riskBadge(level) {
            if (!level) return 'badge-secondary';
            var l = level.toLowerCase();
            if (l === 'low') return 'badge-success';
            if (l === 'medium') return 'badge-warning';
            return 'badge-danger';
        }

        // Filter & search
        document.querySelectorAll('#statusFilters .filter-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('#statusFilters .filter-tab').forEach(function (t) { t.classList.remove('active'); });
                this.classList.add('active');
                var filter = this.dataset.filter;
                document.querySelectorAll('.case-item').forEach(function (item) {
                    if (filter === 'all' || item.dataset.status === filter ||
                        (filter === 'submitted' && (item.dataset.status === 'submitted' || item.dataset.status === 'pending'))) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        document.getElementById('caseSearch').addEventListener('input', function () {
            var q = this.value.toLowerCase();
            document.querySelectorAll('.case-item').forEach(function (item) {
                item.style.display = item.dataset.name.includes(q) ? '' : 'none';
            });
        });

        // Auto-select first case
        // Generate Proposal via Claude API
        function generateProposal(appId, format) {
            var output = document.getElementById('proposalOutput');
            if (output) {
                output.innerHTML = '<div style="padding: 12px; text-align: center;">' +
                    '<div style="width: 24px; height: 24px; border: 3px solid var(--border); border-top-color: var(--yc-orange); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 8px;"></div>' +
                    '<p style="font-size: 13px; color: var(--text-muted);">Generating ' + format.toUpperCase() + ' proposal via Claude API...</p></div>';
            }

            fetch(<?= json_encode($base_url) ?> + '/actions/generate_proposal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'app_id=' + appId + '&format=' + format
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!output) return;
                    if (data.success) {
                        var html = '<div class="card" style="border-color: var(--color-success);">';
                        html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">';
                        html += '<strong style="color: var(--color-success);">✓ Proposal Generated</strong>';
                        html += '<span class="badge badge-secondary">' + escHtml(data.method) + '</span>';
                        html += '</div>';

                        if (data.export_path) {
                            html += '<a href="/' + escHtml(data.export_path) + '" class="btn btn-primary btn-sm" download>Download ' + format.toUpperCase() + '</a> ';
                        }

                        if (format === 'text' && data.proposal_text) {
                            html += '<details style="margin-top: 12px;"><summary style="cursor: pointer; font-size: 13px; color: var(--yc-orange); font-weight: 600;">View Full Proposal</summary>';
                            html += '<pre style="white-space: pre-wrap; font-size: 12px; line-height: 1.6; margin-top: 8px; padding: 12px; background: var(--bg-light); border-radius: var(--radius); max-height: 400px; overflow-y: auto;">' + escHtml(data.proposal_text) + '</pre>';
                            html += '</details>';
                        }

                        html += '</div>';
                        output.innerHTML = html;
                    } else {
                        output.innerHTML = '<div class="alert alert-error">' + escHtml(data.error || 'Generation failed') + '</div>';
                    }
                })
                .catch(function (err) {
                    if (output) {
                        output.innerHTML = '<div class="alert alert-error">Failed to connect to proposal service.</div>';
                    }
                });
        }

        <?php if (!empty($applications)): ?>
            document.querySelector('.case-item').click();
        <?php endif; ?>
    </script>

    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

</body>

</html>