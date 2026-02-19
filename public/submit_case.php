<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'consultant') {
    header('Location: login.html?error=unauthorized');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_case.php');
    exit;
}

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

    // Insert application - ensure status is pending or processing? User said "Customer submits... Consultant creates...". 
    // If consultant creates it, maybe it starts as 'processing'? Let's keep it 'pending' for consistency or 'processing'.
    // Let's stick to 'pending' so it flows through the same logic, or 'processing' since a human agent made it.
    // User flow: "create new case -> AI analyzes". AI needs input.
    $stmt = $pdo->prepare("INSERT INTO applications (customer_id, service_type, amount, purpose, status, consultant_id) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([$customer_id, $service_type, $amount, $purpose, $consultant_id]);
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
                $target_file = $upload_dir . uniqid() . '_' . $name;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $doc_stmt->execute([$application_id, $target_file, $name, $type]);
                }
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
    header('Location: create_case.php?error=database_error&message=' . urlencode($e->getMessage()));
    exit;
}
?>