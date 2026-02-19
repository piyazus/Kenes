<?php
session_start();
require_once 'includes/db.php';

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $iin = trim($_POST['iin'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name) || empty($email) || empty($iin) || empty($phone) || empty($password) || empty($address)) {
        $error_msg = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $error_msg = "Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, and a number.";
    } elseif (strlen($iin) !== 12 || !ctype_digit($iin)) {
        $error_msg = "IIN must be exactly 12 digits.";
    } else {
        $stmt = $pdo->prepare("SELECT customer_id FROM customer_profiles WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_msg = "Email already exists.";
        } else {
            $stmt = $pdo->prepare("SELECT customer_id FROM customer_profiles WHERE iin_number = ?");
            $stmt->execute([$iin]);
            if ($stmt->fetch()) {
                $error_msg = "IIN already exists.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                try {
                    $stmt = $pdo->prepare("INSERT INTO customer_profiles (full_name, email, iin_number, phone_number, password_hash, address) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$full_name, $email, $iin, $phone, $password_hash, $address]);

                    header('Location: login.php?success=registered');
                    exit;
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

$extra_css = '<link rel="stylesheet" href="css/forms.css">';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p class="text-secondary">Join Kenes for financial consultation</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div
                style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <form class="needs-validation" novalidate action="register-customer.php" method="POST">

            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required
                    value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" placeholder="Diyastiger">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required data-validate="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Diyastiger@gmail.com">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="iin">IIN Number</label>
                <input type="text" class="form-control" id="iin" name="iin" required data-validate="iin"
                    value="<?php echo htmlspecialchars($_POST['iin'] ?? ''); ?>" placeholder="123456789012"
                    maxlength="12">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" required data-validate="phone"
                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+77001234567">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required
                    data-validate="password" placeholder="Min. 8 chars, 1 Upper, 1 Lower, 1 Number">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                    data-validate="confirm-password" placeholder="Confirm your password">
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" required
                    placeholder="Your full residential address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                <div class="feedback-message invalid-feedback"></div>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">I agree to the Terms & Conditions</label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Create Account</button>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
                <p class="mt-2"><a href="register-consultant.php">Are you a Consultant?</a></p>
            </div>
        </form>
    </div>
</div>

<script src="js/form-validation.js"></script>
<script src="js/navbar.js"></script>

<?php include 'includes/footer.php'; ?>