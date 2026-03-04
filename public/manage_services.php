<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

$error_msg = '';
$success_msg = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $interest_rate = floatval($_POST['interest_rate'] ?? 0);
        $max_amount = floatval($_POST['max_amount'] ?? 0);
        $duration = trim($_POST['duration'] ?? '');
        $loan_type = trim($_POST['loan_type'] ?? 'general');

        if (empty($name)) {
            $error_msg = 'Service name is required.';
        } elseif ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO services (name, description, interest_rate, max_amount, duration, loan_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $interest_rate ?: null, $max_amount ?: null, $duration, $loan_type]);
            $success_msg = 'Service added successfully.';
        } elseif ($action === 'edit') {
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, interest_rate = ?, max_amount = ?, duration = ?, loan_type = ? WHERE id = ?");
            $stmt->execute([$name, $description, $interest_rate ?: null, $max_amount ?: null, $duration, $loan_type, $id]);
            $success_msg = 'Service updated successfully.';
        }
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE services SET active = NOT active WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = 'Service status updated.';
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = 'Service deleted.';
    }
}

$stmt = $pdo->query("SELECT * FROM services ORDER BY id");
$services = $stmt->fetchAll();

$edit_id = intval($_GET['edit'] ?? 0);
$edit_service = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_service = $stmt->fetch();
}

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/dashboard.css"><link rel="stylesheet" href="' . $base_url . '/css/tables.css"><link rel="stylesheet" href="' . $base_url . '/css/forms.css">';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1><?= __('svc.manage') ?></h1>
            <p>Add, edit, and toggle services available to customers.</p>
        </div>
        <a href="consultant-dashboard.php" class="btn btn-ghost">← Back to Dashboard</a>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <!-- Add/Edit service form -->
    <div class="card" style="margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0;"><?= $edit_service ? 'Edit Service' : __('svc.add_new') ?></h3>
            <?php if ($edit_service): ?>
                <a href="manage_services.php" class="btn btn-ghost btn-sm">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="POST" action="manage_services.php">
            <input type="hidden" name="action" value="<?= $edit_service ? 'edit' : 'add' ?>">
            <?php if ($edit_service): ?>
                <input type="hidden" name="id" value="<?= $edit_service['id'] ?>">
            <?php endif; ?>
            <?php csrfField(); ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label"><?= __('svc.name') ?> *</label>
                    <input type="text" class="form-control" name="name" required placeholder="Service name"
                        value="<?= htmlspecialchars($edit_service['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Loan Type</label>
                    <select class="form-control" name="loan_type">
                        <?php $lt = $edit_service['loan_type'] ?? 'general'; ?>
                        <option value="general" <?= $lt === 'general' ? 'selected' : '' ?>>General</option>
                        <option value="working_capital" <?= $lt === 'working_capital' ? 'selected' : '' ?>>Working Capital
                        </option>
                        <option value="leasing" <?= $lt === 'leasing' ? 'selected' : '' ?>>Leasing</option>
                        <option value="microcredit" <?= $lt === 'microcredit' ? 'selected' : '' ?>>Microcredit</option>
                        <option value="investment" <?= $lt === 'investment' ? 'selected' : '' ?>>Investment</option>
                        <option value="green" <?= $lt === 'green' ? 'selected' : '' ?>>Green</option>
                        <option value="analysis" <?= $lt === 'analysis' ? 'selected' : '' ?>>Analysis</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="2"
                    placeholder="Brief description"><?= htmlspecialchars($edit_service['description'] ?? '') ?></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label"><?= __('svc.rate') ?> (%)</label>
                    <input type="number" class="form-control" name="interest_rate" step="0.01" placeholder="14.00"
                        value="<?= htmlspecialchars($edit_service['interest_rate'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('svc.max_amount') ?> (KZT)</label>
                    <input type="number" class="form-control" name="max_amount" placeholder="50000000"
                        value="<?= htmlspecialchars($edit_service['max_amount'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('svc.duration') ?></label>
                    <input type="text" class="form-control" name="duration" placeholder="1 - 5 Years"
                        value="<?= htmlspecialchars($edit_service['duration'] ?? '') ?>">
                </div>
            </div>
            <button type="submit"
                class="btn btn-primary"><?= $edit_service ? 'Save Changes' : __('svc.add_new') ?></button>
        </form>
    </div>

    <!-- Services table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= __('svc.name') ?></th>
                        <th><?= __('svc.rate') ?></th>
                        <th><?= __('svc.max_amount') ?></th>
                        <th><?= __('svc.duration') ?></th>
                        <th><?= __('svc.status') ?></th>
                        <th><?= __('svc.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $svc): ?>
                        <tr>
                            <td>#<?= $svc['id'] ?></td>
                            <td><strong><?= htmlspecialchars($svc['name']) ?></strong></td>
                            <td><?= $svc['interest_rate'] ? $svc['interest_rate'] . '%' : '—' ?></td>
                            <td><?= $svc['max_amount'] ? number_format($svc['max_amount'], 0, '.', ',') . ' KZT' : '—' ?>
                            </td>
                            <td><?= htmlspecialchars($svc['duration'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= $svc['active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $svc['active'] ? __('svc.active') : __('svc.inactive') ?>
                                </span>
                            </td>
                            <td>
                                <a href="manage_services.php?edit=<?= $svc['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                    <?php csrfField(); ?>
                                    <button type="submit"
                                        class="btn btn-ghost btn-sm"><?= $svc['active'] ? 'Disable' : 'Enable' ?></button>
                                </form>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Delete this service?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                    <?php csrfField(); ?>
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                        style="color: var(--color-error);"><?= __('misc.delete') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= $base_url ?>/js/algorithms.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('.table');
        if (!table) return;
        const headers = table.querySelectorAll('thead th');
        const tbody = table.querySelector('tbody');

        // Helper class to make mergeSort work with objects
        class SortableRow {
            constructor(tr, sortValue) {
                this.tr = tr;
                this.sortValue = sortValue;
            }
            // This makes < and > operators work in JS
            valueOf() {
                return this.sortValue;
            }
        }

        headers.forEach((th, index) => {
            // Exclude Actions column
            if (index === 6) return;

            th.style.cursor = 'pointer';
            th.title = 'Click to sort';

            th.addEventListener('click', () => {
                let rows = Array.from(tbody.querySelectorAll('tr'));

                let sortableArray = rows.map(tr => {
                    let text = tr.children[index].textContent.trim();
                    let val = text.toLowerCase();

                    // Parse IDs and money to numbers
                    let rawNum = text.replace(/,/g, '').replace(/[KZT%#\s]/g, '');
                    if (!isNaN(parseFloat(rawNum)) && isFinite(rawNum)) {
                        val = parseFloat(rawNum);
                    }

                    return new SortableRow(tr, val);
                });

                // Toggle sort direction
                let isAsc = th.dataset.sortAsc === 'true';
                th.dataset.sortAsc = !isAsc;

                // Sort using Merge Sort from algorithms.js
                let sortedArray = mergeSort(sortableArray);

                if (isAsc) {
                    // Since mergeSort creates a new array we just reverse it for desc
                    sortedArray.reverse();
                }

                // Re-render
                tbody.innerHTML = '';
                sortedArray.forEach(item => tbody.appendChild(item.tr));

                // Visual feedback
                headers.forEach(h => h.innerHTML = h.innerHTML.replace(/ [▲▼]$/, ''));
                th.innerHTML += isAsc ? ' ▼' : ' ▲';
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>