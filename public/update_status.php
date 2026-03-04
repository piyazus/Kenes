<?php
/**
 * AJAX endpoint — Update application status or save notes
 */
session_start();
header('Content-Type: application/json');

require_once 'includes/db.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$app_id = intval($_POST['app_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$app_id) {
    echo json_encode(['error' => 'Missing app_id']);
    exit;
}

try {
    if ($action === 'save_notes') {
        $notes = $_POST['notes'] ?? '';
        $stmt = $pdo->prepare("UPDATE applications SET notes = ? WHERE id = ?");
        $stmt->execute([$notes, $app_id]);
        echo json_encode(['success' => true]);
    } else {
        $status = $_POST['status'] ?? '';
        $valid = ['submitted', 'pending', 'processing', 'under_review', 'analyzed', 'sent_to_bank', 'approved', 'rejected', 'bank_approved', 'bank_rejected'];
        if (!in_array($status, $valid)) {
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE applications SET status = ?, consultant_id = ? WHERE id = ?");
        $stmt->execute([$status, $_SESSION['user_id'], $app_id]);

        // Log activity
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, user_type, action, details) VALUES (?, 'consultant', 'status_updated', ?)");
        $stmt->execute([$_SESSION['user_id'], "Application #$app_id status changed to $status"]);

        echo json_encode(['success' => true, 'status' => $status]);
    }
} catch (PDOException $e) {
    error_log('update_status error: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred. Please try again.']);
}