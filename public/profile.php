<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
requireAuth();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$success_msg = '';
$error_msg = '';

// Fetch current profile
if ($user_type === 'customer') {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM consultants WHERE id = ?");
}
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($full_name) || empty($phone)) {
        $error_msg = 'Name and phone are required.';
    } else {
        try {
            if ($user_type === 'customer') {
                $city_region = trim($_POST['city_region'] ?? '');
                $business_name = trim($_POST['business_name'] ?? '');

                $stmt = $pdo->prepare("UPDATE customers SET full_name = ?, phone = ?, city_region = ?, business_name = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $city_region, $business_name, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE consultants SET full_name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $user_id]);
            }

            // Password change
            $current_pass = $_POST['current_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            if (!empty($current_pass) && !empty($new_pass)) {
                if (password_verify($current_pass, $profile['password_hash'])) {
                    if (strlen($new_pass) >= 8) {
                        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                        $table = ($user_type === 'customer') ? 'customers' : 'consultants';
                        $stmt = $pdo->prepare("UPDATE $table SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$new_hash, $user_id]);
                    } else {
                        $error_msg = 'New password must be at least 8 characters.';
                    }
                } else {
                    $error_msg = 'Current password is incorrect.';
                }
            }

            if (empty($error_msg)) {
                $_SESSION['user_name'] = $full_name;
                $success_msg = 'Profile updated successfully.';

                // Re-fetch
                if ($user_type === 'customer') {
                    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM consultants WHERE id = ?");
                }
                $stmt->execute([$user_id]);
                $profile = $stmt->fetch();
            }
        } catch (PDOException $e) {
            error_log('Profile update error: ' . $e->getMessage());
            $error_msg = 'Update failed. Please try again.';
        }
    }
}

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/forms.css">';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card auth-card-wide">
        <div class="auth-header">
            <h1><?= __('profile.title') ?></h1>
            <p class="text-secondary"><?= htmlspecialchars($profile['email']) ?></p>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <?php csrfField(); ?>
            <div class="form-group">
                <label class="form-label" for="full_name"><?= __('auth.full_name') ?></label>
                <input type="text" class="form-control" id="full_name" name="full_name" required
                    value="<?= htmlspecialchars($profile['full_name']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="phone"><?= __('auth.phone') ?></label>
                <input type="tel" class="form-control" id="phone" name="phone" required
                    value="<?= htmlspecialchars($profile['phone']) ?>">
            </div>

            <?php if ($user_type === 'customer'): ?>
                <div class="form-group">
                    <label class="form-label" for="city_region"><?= __('auth.address') ?></label>
                    <input type="text" class="form-control" id="city_region" name="city_region"
                        value="<?= htmlspecialchars($profile['city_region'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="business_name"><?= __('auth.business_name') ?></label>
                    <input type="text" class="form-control" id="business_name" name="business_name"
                        value="<?= htmlspecialchars($profile['business_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('auth.iin') ?></label>
                    <input type="text" class="form-control" disabled
                        value="<?= htmlspecialchars($profile['iin_bin'] ?? '') ?>">
                    <span style="font-size: 12px; color: var(--text-muted);">IIN/BIN cannot be changed</span>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label class="form-label"><?= __('auth.employee_id') ?></label>
                    <input type="text" class="form-control" disabled
                        value="<?= htmlspecialchars($profile['employee_id'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('auth.department') ?></label>
                    <input type="text" class="form-control" disabled
                        value="<?= htmlspecialchars($profile['department'] ?? '') ?>">
                </div>
            <?php endif; ?>

            <hr style="border: none; border-top: 1px solid var(--border); margin: 24px 0;">

            <h3 style="font-size: 1rem;"><?= __('profile.change_pass') ?></h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="current_password"><?= __('profile.current_pass') ?></label>
                    <input type="password" class="form-control" id="current_password" name="current_password"
                        placeholder="Current password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="new_password"><?= __('profile.new_pass') ?></label>
                    <input type="password" class="form-control" id="new_password" name="new_password"
                        placeholder="New password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full"><?= __('profile.save') ?></button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>