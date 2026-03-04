<?php
/**
 * Backend Auth — Register Consultant
 * POST: full_name, email, phone, password, confirm_password, employee_id, department, invite_code
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config.php';
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
$employee_id = trim($_POST['employee_id'] ?? '');
$department = trim($_POST['department'] ?? '');
$invite_code = trim($_POST['invite_code'] ?? '');

// Invite code check
if ($invite_code !== CONSULTANT_INVITE_CODE) {
    echo json_encode(['error' => 'Invalid invite code.']);
    exit;
}

// Validation
if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($employee_id) || empty($department)) {
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

// Check duplicates
$stmt = $pdo->prepare("SELECT id FROM consultants WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Email already registered.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM consultants WHERE employee_id = ?");
$stmt->execute([$employee_id]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Employee ID already registered.']);
    exit;
}

// Insert
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO consultants (full_name, email, phone, password_hash, employee_id, department) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$full_name, $email, $phone, $password_hash, $employee_id, $department]);

    echo json_encode(['success' => true, 'redirect' => '/login.php?success=registered']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
