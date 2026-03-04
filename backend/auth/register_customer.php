<?php
/**
 * Backend Auth — Register Customer
 * POST: full_name, email, phone, password, confirm_password, city_region, business_name, business_type, iin_bin
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../public/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$city_region = trim($_POST['city_region'] ?? '');
$business_name = trim($_POST['business_name'] ?? '');
$business_type = trim($_POST['business_type'] ?? '');
$iin_bin = trim($_POST['iin_bin'] ?? '');

// Validation
if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($iin_bin)) {
    echo json_encode(['error' => 'Please fill in all required fields.']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['error' => 'Passwords do not match.']);
    exit;
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    echo json_encode(['error' => 'Password must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number.']);
    exit;
}

if (strlen($iin_bin) !== 12 || !ctype_digit($iin_bin)) {
    echo json_encode(['error' => 'IIN/BIN must be exactly 12 digits.']);
    exit;
}

// Check duplicates
$stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Email already registered.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM customers WHERE iin_bin = ?");
$stmt->execute([$iin_bin]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'IIN/BIN already registered.']);
    exit;
}

// Insert
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO customers (full_name, email, phone, password_hash, city_region, business_name, business_type, iin_bin) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$full_name, $email, $phone, $password_hash, $city_region, $business_name, $business_type, $iin_bin]);

    $userId = $pdo->lastInsertId();

    // Auto-login
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_type'] = 'customer';
    $_SESSION['iin_bin'] = $iin_bin;
    $_SESSION['business_name'] = $business_name;
    $_SESSION['city_region'] = $city_region;

    echo json_encode(['success' => true, 'redirect' => '/customer-dashboard.php']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
