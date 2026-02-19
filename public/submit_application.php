<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: login.php?error=unauthorized');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: application.php');
    exit;
}

$service_type = trim($_POST['service_type'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$purpose = trim($_POST['purpose'] ?? '');

if (empty($service_type) || $amount <= 0 || empty($purpose)) {
    header('Location: application.php?error=empty_fields');
    exit;
}

$customer_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO applications (customer_id, service_type, amount, purpose, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$customer_id, $service_type, $amount, $purpose]);
    $application_id = $pdo->lastInsertId();

    // Handle file uploads
    if (isset($_FILES['documents'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_count = count($_FILES['documents']['name']);

        $doc_stmt = $pdo->prepare("INSERT INTO application_documents (application_id, file_path, file_name, file_type) VALUES (?, ?, ?, ?)");

        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['documents']['tmp_name'][$i];
                $name = basename($_FILES['documents']['name'][$i]);
                $type = $_FILES['documents']['type'][$i];

                // unique filename
                $target_file = $upload_dir . uniqid() . '_' . $name;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $doc_stmt->execute([$application_id, $target_file, $name, $type]);
                }
            }
        }
    }

    $pdo->commit();

    header('Location: customer-dashboard.php?success=application_submitted');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: application.php?error=database_error&message=' . urlencode($e->getMessage()));
    exit;
}
?>