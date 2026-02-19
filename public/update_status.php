<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'consultant') {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: consultant-dashboard.php');
    exit;
}

$app_id = intval($_POST['app_id'] ?? 0);
$status = $_POST['status'] ?? '';
$consultant_id = $_SESSION['user_id'];

$allowed_statuses = ['pending', 'processing', 'analyzed', 'approved', 'rejected', 'sent_to_bank', 'bank_approved', 'bank_rejected'];

if ($app_id <= 0 || !in_array($status, $allowed_statuses)) {
    header('Location: consultant-dashboard.php?error=invalid_input');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE applications SET status = ?, consultant_id = ? WHERE id = ?");
    $stmt->execute([$status, $consultant_id, $app_id]);

    header('Location: consultant-dashboard.php?success=status_updated');
    exit;
} catch (PDOException $e) {
    header('Location: consultant-dashboard.php?error=database_error');
    exit;
}
?>