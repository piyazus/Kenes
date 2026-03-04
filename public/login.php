<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        $sql = "SELECT id, full_name, email, password_hash, 'customer' as user_type, city_region, business_name, iin_bin, NULL as department, NULL as employee_id 
                FROM customers WHERE email = ?
                UNION ALL
                SELECT id, full_name, email, password_hash, 'consultant' as user_type, NULL, NULL, NULL, department, employee_id 
                FROM consultants WHERE email = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            if ($user['user_type'] === 'customer') {
                $_SESSION['city_region'] = $user['city_region'];
                $_SESSION['business_name'] = $user['business_name'];
                $_SESSION['iin_bin'] = $user['iin_bin'];
            } else {
                $_SESSION['department'] = $user['department'];
                $_SESSION['employee_id'] = $user['employee_id'];
            }

            if (isset($_POST['remember'])) {
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + (86400 * 30), $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }

            header('Location: ' . ($user['user_type'] === 'consultant' ? 'consultant-dashboard.php' : 'customer-dashboard.php'));
            exit;
        } else {
            $error_msg = "Invalid email or password.";
        }
    }
}

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/forms.css">';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><?= __('auth.welcome_back') ?></h1>
            <p class="text-secondary"><?= __('auth.sign_in_sub') ?></p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
            <div class="alert alert-success">Registration successful! Please sign in.</div>
        <?php endif; ?>

        <form class="needs-validation" novalidate action="login.php" method="POST">
            <?php csrfField(); ?>
            <div class="form-group">
                <label class="form-label" for="email"><?= __('auth.email') ?></label>
                <input type="email" class="form-control" id="email" name="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label class="form-label" for="password"><?= __('auth.password') ?></label>
                <input type="password" class="form-control" id="password" name="password" required
                    placeholder="••••••••">
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember"><?= __('auth.remember') ?></label>
            </div>

            <button type="submit" class="btn btn-primary btn-full"><?= __('auth.sign_in') ?></button>

            <div class="auth-footer">
                <p><?= __('auth.no_account') ?> <a href="register-customer.php"><?= __('auth.register') ?></a></p>
                <p class="mt-1"><a href="register-consultant.php"
                        style="color: var(--text-muted); font-size: 12px;"><?= __('auth.consultant_login') ?></a></p>
            </div>
        </form>
    </div>
</div>

<script src="<?= $base_url ?>/js/form-validation.js"></script>

<?php include 'includes/footer.php'; ?>