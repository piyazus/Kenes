<?php
/**
 * Backend Auth — Login
 * POST: email, password
 * Returns JSON response + sets session
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../public/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['error' => 'Please fill in all fields.']);
    exit;
}

// Search both tables
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

    // Remember me
    if (isset($_POST['remember'])) {
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), time() + (86400 * 30), $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }

    $redirect = ($user['user_type'] === 'consultant') ? '/consultant-dashboard.php' : '/customer-dashboard.php';
    echo json_encode(['success' => true, 'redirect' => $redirect, 'user_type' => $user['user_type']]);
} else {
    echo json_encode(['error' => 'Invalid email or password.']);
}
