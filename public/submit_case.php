<?php
/**
 * Submit Case endpoint (consultant creates case on behalf of customer)
 * Fixed: corrected include path, auth redirect, file upload security.
 */
session_start();
require_once 'includes/db.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_case.php');
    exit;
}

verifyCsrf();

$customer_id = intval($_POST['customer_id'] ?? 0);
$service_type = trim($_POST['service_type'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$purpose = trim($_POST['purpose'] ?? '');
$consultant_id = $_SESSION['user_id'];

if ($customer_id <= 0 || empty($service_type) || $amount <= 0 || empty($purpose)) {
    header('Location: create_case.php?error=empty_fields');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO applications (customer_id, service_type, amount, purpose, status, consultant_id) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([$customer_id, $service_type, $amount, $purpose, $consultant_id]);
    $application_id = $pdo->lastInsertId();

    // Handle file uploads with security checks
    if (isset($_FILES['documents'])) {
        $upload_base = __DIR__ . '/../uploads/' . $customer_id . '/' . $application_id . '/';
        if (!is_dir($upload_base)) {
            mkdir($upload_base, 0755, true);
        }

        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 10 * 1024 * 1024; // 10MB

        $file_count = count($_FILES['documents']['name']);
        $doc_stmt = $pdo->prepare("INSERT INTO application_documents (application_id, file_path, file_name, document_type, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");

        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK)
                continue;

            $tmpName = $_FILES['documents']['tmp_name'][$i];
            $fileName = $_FILES['documents']['name'][$i];
            $fileSize = $_FILES['documents']['size'][$i];
            $mimeType = mime_content_type($tmpName);

            if (!in_array($mimeType, $allowed_types))
                continue;
            if ($fileSize > $max_size)
                continue;

            $safeName = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $destPath = $upload_base . $safeName;

            if (move_uploaded_file($tmpName, $destPath)) {
                $relativePath = 'uploads/' . $customer_id . '/' . $application_id . '/' . $safeName;
                $doc_stmt->execute([$application_id, $relativePath, $fileName, 'other', $mimeType, $fileSize]);
            }
        }
    }

    $pdo->commit();

    header('Location: consultant-dashboard.php?success=case_created');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('submit_case error: ' . $e->getMessage());
    header('Location: create_case.php?error=database_error');
    exit;
}
?>