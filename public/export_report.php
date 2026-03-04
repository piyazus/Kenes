<?php
/**
 * Export Report — generates report page (placeholder for DOCX/PPTX when Composer deps are installed)
 */
session_start();
require_once 'includes/db.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

$app_id = intval($_GET['app_id'] ?? 0);
$format = $_GET['format'] ?? 'view';

if (!$app_id) {
    die('Missing application ID.');
}

// Fetch application + customer + proposal
$stmt = $pdo->prepare("SELECT a.*, c.full_name, c.email as customer_email, c.phone as customer_phone, c.business_name, c.iin_bin, c.city_region,
                        s.name as service_name, s.interest_rate, s.max_amount, s.duration as service_duration
                        FROM applications a 
                        JOIN customers c ON a.customer_id = c.id
                        LEFT JOIN services s ON a.service_id = s.id
                        WHERE a.id = ?");
$stmt->execute([$app_id]);
$app = $stmt->fetch();

if (!$app) {
    die('Application not found.');
}

$stmt = $pdo->prepare("SELECT * FROM proposals WHERE application_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$app_id]);
$proposal = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM application_documents WHERE application_id = ?");
$stmt->execute([$app_id]);
$docs = $stmt->fetchAll();

// For DOCX/PPTX — check if Composer dependencies are available
$vendor_autoload = __DIR__ . '/../vendor/autoload.php';
$has_composer = file_exists($vendor_autoload);

if ($format === 'docx' && $has_composer) {
    require_once $vendor_autoload;

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    $section->addTitle('Kenes — Loan Application Report', 1);
    $section->addText('Application #APP-' . str_pad($app['id'], 4, '0', STR_PAD_LEFT));
    $section->addTextBreak();

    $section->addTitle('Customer Information', 2);
    $section->addText('Name: ' . $app['full_name']);
    $section->addText('Business: ' . $app['business_name']);
    $section->addText('IIN/BIN: ' . $app['iin_bin']);
    $section->addText('City: ' . $app['city_region']);
    $section->addTextBreak();

    $section->addTitle('Application Details', 2);
    $section->addText('Service: ' . ($app['service_name'] ?? 'N/A'));
    $section->addText('Amount Requested: ' . number_format($app['amount']) . ' KZT');
    $section->addText('Status: ' . ucfirst(str_replace('_', ' ', $app['status'])));

    if ($proposal) {
        $section->addTextBreak();
        $section->addTitle('AI Analysis', 2);
        $section->addText('Score: ' . $proposal['scoring_points'] . '/100');
        $section->addText('Risk Level: ' . $proposal['risk_level']);
        $section->addText('Recommended Amount: ' . number_format($proposal['recommended_amount']) . ' KZT');
        $section->addTextBreak();
        $section->addText($proposal['ai_summary']);
    }

    $fileName = 'kenes_report_' . $app_id . '.docx';
    $tempFile = sys_get_temp_dir() . '/' . $fileName;

    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempFile);

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    readfile($tempFile);
    unlink($tempFile);
    exit;
}

// Default — render HTML report view
require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/dashboard.css"><link rel="stylesheet" href="' . $base_url . '/css/tables.css">';
include 'includes/header.php';
?>

<div class="container" style="padding: 48px 24px 80px; max-width: 800px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1>Application Report</h1>
        <div style="display: flex; gap: 8px;">
            <?php if ($has_composer): ?>
                <a href="export_report.php?app_id=<?= $app_id ?>&format=docx" class="btn btn-primary btn-sm">Download
                    DOCX</a>
            <?php else: ?>
                <button class="btn btn-ghost btn-sm" disabled title="Run 'composer install' to enable">DOCX (needs
                    Composer)</button>
            <?php endif; ?>
            <a href="consultant-dashboard.php" class="btn btn-ghost btn-sm">← Back</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 16px;">
        <h3>Customer Profile</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 14px; margin-top: 12px;">
            <div><strong>Name:</strong> <?= htmlspecialchars($app['full_name']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($app['customer_email']) ?></div>
            <div><strong>Phone:</strong> <?= htmlspecialchars($app['customer_phone']) ?></div>
            <div><strong>IIN/BIN:</strong> <?= htmlspecialchars($app['iin_bin']) ?></div>
            <div><strong>City:</strong> <?= htmlspecialchars($app['city_region'] ?? '—') ?></div>
            <div><strong>Business:</strong> <?= htmlspecialchars($app['business_name'] ?? '—') ?></div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 16px;">
        <h3>Application Details</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 14px; margin-top: 12px;">
            <div><strong>App ID:</strong> #APP-<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?></div>
            <div><strong>Service:</strong> <?= htmlspecialchars($app['service_name'] ?? 'N/A') ?></div>
            <div><strong>Amount:</strong> <?= number_format($app['amount']) ?> KZT</div>
            <div><strong>Rate:</strong> <?= $app['interest_rate'] ? $app['interest_rate'] . '%' : '—' ?></div>
            <div><strong>Status:</strong> <span
                    class="badge badge-info"><?= ucfirst(str_replace('_', ' ', $app['status'])) ?></span></div>
            <div><strong>Submitted:</strong> <?= date('M d, Y', strtotime($app['created_at'])) ?></div>
        </div>
    </div>

    <?php if ($proposal): ?>
        <div class="card" style="margin-bottom: 16px; border-color: var(--yc-orange);">
            <h3>AI Analysis</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 12px;">
                <div style="text-align: center;">
                    <div style="font-size: 36px; font-weight: 800; color: var(--yc-orange);">
                        <?= $proposal['scoring_points'] ?>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted);">Score / 100</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: 700;"><?= htmlspecialchars($proposal['risk_level']) ?></div>
                    <div style="font-size: 12px; color: var(--text-muted);">Risk Level</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: 700;"><?= number_format($proposal['recommended_amount']) ?>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted);">Recommended KZT</div>
                </div>
            </div>
            <p style="margin-top: 16px; font-size: 14px; white-space: pre-line;">
                <?= htmlspecialchars($proposal['ai_summary']) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (!empty($docs)): ?>
        <div class="card">
            <h3>Documents (<?= count($docs) ?>)</h3>
            <?php foreach ($docs as $doc): ?>
                <div class="file-item" style="margin-top: 8px;">
                    <span class="file-item-name">
                        <?= htmlspecialchars($doc['file_name']) ?>
                        <span class="badge badge-secondary"><?= htmlspecialchars($doc['document_type']) ?></span>
                    </span>
                    <span style="font-size: 12px; color: var(--text-muted);"><?= round($doc['file_size'] / 1024) ?> KB</span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>