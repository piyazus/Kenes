<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "damu_loan_consultant";

$error_msg = "";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        $sql = "SELECT customer_id as id, full_name, email, password_hash, 'customer' as user_type, NULL as department 
                FROM customer_profiles WHERE email = ?
                UNION ALL
                SELECT consultant_id as id, full_name, email, password_hash, 'consultant' as user_type, department 
                FROM consultant_profiles WHERE email = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['customer_id'] ?? $user['consultant_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            if (isset($_POST['remember'])) {
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + (86400 * 30), $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }

            if ($user['user_type'] === 'consultant') {
                $_SESSION['department'] = $user['department'];
                header('Location: consultant-dashboard.php');
            } else {
                header('Location: customer-dashboard.php');
            }
            exit;
        } else {
            $error_msg = "Invalid email or password.";
        }
    }
}

$extra_css = '<link rel="stylesheet" href="css/forms.css">';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p class="text-secondary">Please sign in to your account</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div
                style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <form class="needs-validation" novalidate action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Diyastiger@gmail.com">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="123456">
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Sign In</button>

            <div class="auth-footer">
                <p><a href="#">Forgot password?</a></p>
                <p class="mt-2">Don't have an account? <a href="register-customer.php">Register</a></p>
                <p class="mt-1"><a href="register-consultant.php"
                        style="color: var(--color-gray-500); font-size: 12px;">Consultant Login</a></p>
            </div>
        </form>
    </div>
</div>

<script src="js/form-validation.js"></script>

<?php include 'includes/footer.php'; ?>