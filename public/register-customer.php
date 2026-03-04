<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $city_region = trim($_POST['city_region'] ?? '');
    $business_name = trim($_POST['business_name'] ?? '');
    $business_type = trim($_POST['business_type'] ?? '');
    $iin_bin = trim($_POST['iin_bin'] ?? '');

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($iin_bin)) {
        $error_msg = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $error_msg = "Password must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number.";
    } elseif (strlen($iin_bin) !== 12 || !ctype_digit($iin_bin)) {
        $error_msg = "IIN/BIN must be exactly 12 digits.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_msg = "Email already registered.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE iin_bin = ?");
            $stmt->execute([$iin_bin]);
            if ($stmt->fetch()) {
                $error_msg = "IIN/BIN already registered.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = $pdo->prepare("INSERT INTO customers (full_name, email, phone, password_hash, city_region, business_name, business_type, iin_bin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$full_name, $email, $phone, $password_hash, $city_region, $business_name, $business_type, $iin_bin]);

                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_name'] = $full_name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_type'] = 'customer';
                    $_SESSION['iin_bin'] = $iin_bin;
                    $_SESSION['business_name'] = $business_name;
                    $_SESSION['city_region'] = $city_region;

                    header('Location: customer-dashboard.php');
                    exit;
                } catch (PDOException $e) {
                    error_log('Registration error: ' . $e->getMessage());
                    $error_msg = "Registration failed. Please try again.";
                }
            }
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
            <h1><?= __('auth.create_account') ?></h1>
            <p class="text-secondary"><?= __('auth.join_sub') ?></p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form class="needs-validation" novalidate action="register-customer.php" method="POST">
            <?php csrfField(); ?>

            <div class="form-group">
                <label class="form-label" for="full_name"><?= __('auth.full_name') ?> *</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required
                    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="Alikhan Serikbayev">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email"><?= __('auth.email') ?> *</label>
                <input type="email" class="form-control" id="email" name="email" required data-validate="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="name@example.com">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone"><?= __('auth.phone') ?> *</label>
                <input type="tel" class="form-control" id="phone" name="phone" required data-validate="phone"
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+77001234567">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="password"><?= __('auth.password') ?> *</label>
                    <input type="password" class="form-control" id="password" name="password" required
                        data-validate="password" placeholder="Min. 8 chars">
                    <div class="feedback-message invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password"><?= __('auth.confirm_password') ?> *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                        data-validate="confirm-password" placeholder="Repeat password">
                    <div class="feedback-message invalid-feedback"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="city_region"><?= __('auth.address') ?></label>
                <input type="text" class="form-control" id="city_region" name="city_region"
                    value="<?= htmlspecialchars($_POST['city_region'] ?? '') ?>" placeholder="Almaty">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="business_name"><?= __('auth.business_name') ?></label>
                    <input type="text" class="form-control" id="business_name" name="business_name"
                        value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>" placeholder="Your Business LLC">
                </div>
                <div class="form-group">
                    <label class="form-label" for="business_type"><?= __('auth.business_type') ?></label>
                    <select class="form-control" id="business_type" name="business_type">
                        <option value="">Select type</option>
                        <option value="ip" <?= ($_POST['business_type'] ?? '') === 'ip' ? 'selected' : '' ?>>Individual
                            Entrepreneur (IP)</option>
                        <option value="too" <?= ($_POST['business_type'] ?? '') === 'too' ? 'selected' : '' ?>>TOO (LLC)
                        </option>
                        <option value="ao" <?= ($_POST['business_type'] ?? '') === 'ao' ? 'selected' : '' ?>>AO (JSC)
                        </option>
                        <option value="kh" <?= ($_POST['business_type'] ?? '') === 'kh' ? 'selected' : '' ?>>Peasant Farm
                            (KH)</option>
                        <option value="other" <?= ($_POST['business_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="iin_bin"><?= __('auth.iin') ?> *</label>
                <input type="text" class="form-control" id="iin_bin" name="iin_bin" required data-validate="iin"
                    value="<?= htmlspecialchars($_POST['iin_bin'] ?? '') ?>" placeholder="123456789012" maxlength="12">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms"><?= __('auth.terms_agree') ?></label>
            </div>

            <button type="submit" class="btn btn-primary btn-full"><?= __('auth.create_account') ?></button>

            <div class="auth-footer">
                <p><?= __('auth.has_account') ?> <a href="login.php"><?= __('auth.sign_in') ?></a></p>
                <p class="mt-1"><a href="register-consultant.php"><?= __('auth.is_consultant') ?></a></p>
            </div>
        </form>
    </div>
</div>

<script src="<?= $base_url ?>/js/form-validation.js"></script>

<?php include 'includes/footer.php'; ?>