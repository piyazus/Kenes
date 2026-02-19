<?php
session_start();
require_once 'db.php';
require_once 'ai_module.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'consultant') {
    header('Location: login.html');
    exit;
}

$app_id = intval($_POST['app_id'] ?? 0);

if ($app_id <= 0) {
    header('Location: consultant-dashboard.php?error=invalid_id');
    exit;
}

try {
    // Fetch application details
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$app_id]);
    $app = $stmt->fetch();

    if (!$app) {
        die("Application not found.");
    }

    // Run AI Analysis
    $analysis = analyzeApplication($app['id'], $app['service_type'], $app['amount'], $app['purpose']);

    // Create Proposal Record
    $proposal_text = "Based on the AI analysis (Risk Score: " . json_decode($analysis['analysis_json'])->risk_score . "), we recommend the following:\n\n" .
        "Status: " . ucfirst($analysis['status']) . "\n" .
        "Reason: " . $analysis['reason'];

    $prop_stmt = $pdo->prepare("INSERT INTO proposals (application_id, consultant_id, ai_analysis_json, proposal_text, status) VALUES (?, ?, ?, ?, 'draft')");
    $prop_stmt->execute([$app_id, $_SESSION['user_id'], $analysis['analysis_json'], $proposal_text]);

    // Update Application Status
    $update_stmt = $pdo->prepare("UPDATE applications SET status = 'analyzed' WHERE id = ?");
    $update_stmt->execute([$app_id]);

    header('Location: consultant-dashboard.php?success=analysis_complete');
    exit;

} catch (Exception $e) {
    header('Location: consultant-dashboard.php?error=analysis_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>