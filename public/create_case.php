<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

$error_msg = '';
$success_msg = '';

// Fetch customers for dropdown
$stmt = $pdo->query("SELECT id, full_name, business_name, email FROM customers ORDER BY full_name");
$customers = $stmt->fetchAll();

// Fetch services
$stmt = $pdo->query("SELECT id, name FROM services WHERE active = 1 ORDER BY id");
$services = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $service_id = intval($_POST['service_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if (!$customer_id || !$service_id || $amount < 100000) {
        $error_msg = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("SELECT loan_type FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $svc = $stmt->fetch();

        $stmt = $pdo->prepare("INSERT INTO applications (customer_id, service_id, service_type, amount, notes, status, consultant_id) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([$customer_id, $service_id, $svc['loan_type'] ?? 'general', $amount, $notes, $_SESSION['user_id']]);

        $success_msg = 'Case #APP-' . str_pad($pdo->lastInsertId(), 4, '0', STR_PAD_LEFT) . ' created successfully.';
    }
}

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/forms.css"><link rel="stylesheet" href="' . $base_url . '/css/dashboard.css">';
include 'includes/header.php';
?>

<div class="dashboard-container" style="max-width: 600px;">
    <div class="dashboard-header">
        <div>
            <h1>Create New Case</h1>
            <p>Manually create an application on behalf of a customer.</p>
        </div>
        <a href="consultant-dashboard.php" class="btn btn-ghost">← Dashboard</a>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <?php csrfField(); ?>
            <div class="form-group">
                <label class="form-label">Customer *</label>
                <select class="form-control" name="customer_id" required>
                    <option value="">Select customer</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name']) ?> —
                            <?= htmlspecialchars($c['business_name'] ?? $c['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Service *</label>
                <select class="form-control" name="service_id" required>
                    <option value="">Select service</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Amount (KZT) *</label>
                <input type="number" class="form-control" name="amount" required min="100000" placeholder="5000000">
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea class="form-control" name="notes" rows="3" placeholder="Case notes..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Create Case</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>