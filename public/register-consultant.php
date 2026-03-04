<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
require_once __DIR__ . '/../config.php';

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $employee_id = trim($_POST['employee_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $invite_code = trim($_POST['invite_code'] ?? '');

    if ($invite_code !== CONSULTANT_INVITE_CODE) {
        $error_msg = "Invalid invite code.";
    } elseif (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($employee_id) || empty($department)) {
        $error_msg = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $error_msg = "Password must be 8+ chars with uppercase, lowercase, and a number.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM consultants WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_msg = "Email already registered.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM consultants WHERE employee_id = ?");
            $stmt->execute([$employee_id]);
            if ($stmt->fetch()) {
                $error_msg = "Employee ID already registered.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = $pdo->prepare("INSERT INTO consultants (full_name, email, phone, password_hash, employee_id, department) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$full_name, $email, $phone, $password_hash, $employee_id, $department]);
                    header('Location: login.php?success=registered');
                    exit;
                } catch (PDOException $e) {
                    error_log('Consultant registration error: ' . $e->getMessage());
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
            <h1><?= __('auth.register_consultant') ?></h1>
            <p class="text-secondary">Consultant access requires an invite code</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form class="needs-validation" novalidate action="register-consultant.php" method="POST">
            <?php csrfField(); ?>

            <div class="form-group">
                <label class="form-label" for="invite_code"><?= __('auth.invite_code') ?> *</label>
                <input type="text" class="form-control" id="invite_code" name="invite_code" required
                    value="<?= htmlspecialchars($_POST['invite_code'] ?? '') ?>" placeholder="Enter invite code">
            </div>

            <div class="form-group">
                <label class="form-label" for="full_name"><?= __('auth.full_name') ?> *</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required
                    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="Full name">
            </div>

            <div class="form-group">
                <label class="form-label" for="email"><?= __('auth.email') ?> *</label>
                <input type="email" class="form-control" id="email" name="email" required data-validate="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="name@kenes.kz">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone"><?= __('auth.phone') ?> *</label>
                <input type="tel" class="form-control" id="phone" name="phone" required
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+77001234567">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="employee_id"><?= __('auth.employee_id') ?> *</label>
                    <input type="text" class="form-control" id="employee_id" name="employee_id" required
                        value="<?= htmlspecialchars($_POST['employee_id'] ?? '') ?>" placeholder="EMP-001">
                </div>
                <div class="form-group">
                    <label class="form-label" for="department"><?= __('auth.department') ?> *</label>
                    <select class="form-control" id="department" name="department" required>
                        <option value="">Select department</option>
                        <option value="Lending" <?= ($_POST['department'] ?? '') === 'Lending' ? 'selected' : '' ?>>Lending
                        </option>
                        <option value="Risk Analysis" <?= ($_POST['department'] ?? '') === 'Risk Analysis' ? 'selected' : '' ?>>Risk Analysis</option>
                        <option value="Client Relations" <?= ($_POST['department'] ?? '') === 'Client Relations' ? 'selected' : '' ?>>Client Relations</option>
                        <option value="Operations" <?= ($_POST['department'] ?? '') === 'Operations' ? 'selected' : '' ?>>
                            Operations</option>
                    </select>
                </div>
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

            <button type="submit" class="btn btn-primary btn-full"><?= __('auth.register_consultant') ?></button>

            <div class="auth-footer">
                <p><?= __('auth.has_account') ?> <a href="login.php"><?= __('auth.sign_in') ?></a></p>
            </div>
        </form>
    </div>
</div>

<script src="<?= $base_url ?>/js/form-validation.js"></script>

<?php include 'includes/footer.php'; ?>