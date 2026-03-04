<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth_guard.php';
requireAuth('customer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: application.php');
    exit;
}

verifyCsrf();

$customer_id = $_SESSION['user_id'];
$service_id = intval($_POST['service_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

if (!$service_id || $amount < 100000) {
    $_SESSION['flash_error'] = 'Invalid service or amount.';
    header('Location: application.php');
    exit;
}

// Get service type
$stmt = $pdo->prepare("SELECT name, loan_type FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();
$service_type = $service['loan_type'] ?? 'general';

try {
    $pdo->beginTransaction();

    // Insert application
    $stmt = $pdo->prepare(
        "INSERT INTO applications (customer_id, service_id, service_type, amount, purpose, notes, status) 
         VALUES (?, ?, ?, ?, ?, ?, 'submitted')"
    );
    $purpose = $service['name'] ?? 'Loan application';
    $stmt->execute([$customer_id, $service_id, $service_type, $amount, $purpose, $notes]);
    $application_id = $pdo->lastInsertId();

    // Create upload directory
    $upload_base = __DIR__ . '/../uploads/' . $customer_id . '/' . $application_id . '/';
    if (!is_dir($upload_base)) {
        mkdir($upload_base, 0755, true);
    }

    // Handle typed document uploads
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $max_size = 10 * 1024 * 1024; // 10MB

    if (isset($_FILES['documents'])) {
        foreach ($_FILES['documents']['name'] as $docType => $fileName) {
            if (empty($fileName))
                continue;

            $tmpName = $_FILES['documents']['tmp_name'][$docType];
            $fileSize = $_FILES['documents']['size'][$docType];
            $mimeType = mime_content_type($tmpName);

            if (!in_array($mimeType, $allowed_types))
                continue;
            if ($fileSize > $max_size)
                continue;

            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $destPath = $upload_base . $safeName;

            if (move_uploaded_file($tmpName, $destPath)) {
                $relativePath = 'uploads/' . $customer_id . '/' . $application_id . '/' . $safeName;
                $stmt = $pdo->prepare(
                    "INSERT INTO application_documents (application_id, file_path, file_name, document_type, file_type, file_size) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$application_id, $relativePath, $fileName, $docType, $mimeType, $fileSize]);
            }
        }
    }

    // Handle extra drag-drop files
    if (isset($_FILES['extra_documents'])) {
        for ($i = 0; $i < count($_FILES['extra_documents']['name']); $i++) {
            $fileName = $_FILES['extra_documents']['name'][$i];
            if (empty($fileName))
                continue;

            $tmpName = $_FILES['extra_documents']['tmp_name'][$i];
            $fileSize = $_FILES['extra_documents']['size'][$i];
            $mimeType = mime_content_type($tmpName);

            if (!in_array($mimeType, $allowed_types))
                continue;
            if ($fileSize > $max_size)
                continue;

            $safeName = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $destPath = $upload_base . $safeName;

            if (move_uploaded_file($tmpName, $destPath)) {
                $relativePath = 'uploads/' . $customer_id . '/' . $application_id . '/' . $safeName;
                $stmt = $pdo->prepare(
                    "INSERT INTO application_documents (application_id, file_path, file_name, document_type, file_type, file_size) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$application_id, $relativePath, $fileName, 'other', $mimeType, $fileSize]);
            }
        }
    }

    // Log activity
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, user_type, action, details) VALUES (?, 'customer', 'application_submitted', ?)");
    $stmt->execute([$customer_id, 'Application #' . $application_id . ' submitted for ' . ($service['name'] ?? 'service')]);

    $pdo->commit();

    $_SESSION['flash_success'] = 'Application submitted successfully!';
    header('Location: customer-dashboard.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Application submit error: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Failed to submit application. Please try again.';
    header('Location: application.php');
    exit;
}