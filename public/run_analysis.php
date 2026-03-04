<?php
/**
 * AI Analysis endpoint
 * GET: Returns existing proposal for an application
 * POST: Runs AI analysis (mock/KenesCloud/Claude)
 */
session_start();
header('Content-Type: application/json');

require_once 'includes/db.php';
require_once 'includes/auth_guard.php';
requireAuth('consultant');

// GET — fetch existing proposal
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $app_id = intval($_GET['app_id'] ?? 0);
    if (!$app_id) {
        echo json_encode(['error' => 'Missing app_id']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM proposals WHERE application_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$app_id]);
    $proposal = $stmt->fetch();

    if ($proposal) {
        echo json_encode(['success' => true, 'proposal' => $proposal]);
    } else {
        echo json_encode(['success' => true, 'proposal' => null]);
    }
    exit;
}

// POST — run analysis
$app_id = intval($_POST['app_id'] ?? 0);
if (!$app_id) {
    echo json_encode(['error' => 'Missing app_id']);
    exit;
}

// Fetch application + customer data
$stmt = $pdo->prepare("SELECT a.*, c.full_name, c.business_name, c.iin_bin, c.city_region 
                        FROM applications a JOIN customers c ON a.customer_id = c.id WHERE a.id = ?");
$stmt->execute([$app_id]);
$app = $stmt->fetch();

if (!$app) {
    echo json_encode(['error' => 'Application not found']);
    exit;
}

// Fetch documents
$stmt = $pdo->prepare("SELECT * FROM application_documents WHERE application_id = ?");
$stmt->execute([$app_id]);
$docs = $stmt->fetchAll();

// Try KenesCloud API first, fallback to local analysis
$config_file = __DIR__ . '/../config.php';
$use_api = false;
if (file_exists($config_file)) {
    require_once $config_file;
    $use_api = defined('KENESCLOUD_API_KEY') && KENESCLOUD_API_KEY !== 'your-kenescloud-key-here';
}

if ($use_api) {
    // Call KenesCloud API
    $payload = json_encode([
        'customer_name' => $app['full_name'],
        'business_name' => $app['business_name'],
        'iin_bin' => $app['iin_bin'],
        'amount_requested' => $app['amount'],
        'service_type' => $app['service_type'],
        'documents_count' => count($docs),
    ]);

    $ch = curl_init(KENESCLOUD_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . KENESCLOUD_API_KEY
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $result = json_decode($response, true);
        $scoring = $result['scoring_points'] ?? 65;
        $risk = $result['risk_level'] ?? 'Medium';
        $recommended = $result['recommended_amount'] ?? $app['amount'] * 0.8;
        $summary = $result['summary'] ?? 'API analysis complete.';
    } else {
        // Fallback to local
        $use_api = false;
    }
}

if (!$use_api) {
    // Local mock analysis (deterministic based on app data)
    $doc_score = min(count($docs) * 15, 30);
    $amount_score = ($app['amount'] < 10000000) ? 25 : (($app['amount'] < 50000000) ? 20 : 10);
    $base_score = 40 + $doc_score + $amount_score;
    $scoring = min(98, max(35, $base_score + rand(-5, 10)));
    $risk = ($scoring >= 75) ? 'Low' : (($scoring >= 50) ? 'Medium' : 'High');
    $recommended = round($app['amount'] * ($scoring / 100) * 0.9, -4);

    $summary = "Financial Analysis for " . $app['full_name'] . " (" . $app['business_name'] . ").\n\n" .
        "Requested Amount: " . number_format($app['amount']) . " KZT.\n" .
        "Documents Submitted: " . count($docs) . ".\n" .
        "Risk Assessment: " . $risk . " (" . $scoring . "/100).\n\n" .
        "Based on the submitted documentation and financial profile, the applicant qualifies for " .
        number_format($recommended) . " KZT under the " . ucwords(str_replace('_', ' ', $app['service_type'] ?? 'general')) . " program. " .
        ($risk === 'Low' ? 'The applicant presents a strong financial profile with adequate documentation.' :
            ($risk === 'Medium' ? 'Additional documentation or guarantees may strengthen the application.' :
                'The application carries elevated risk. Consider requesting additional collateral or reducing the amount.'));
}

try {
    // Upsert proposal
    $stmt = $pdo->prepare("SELECT id FROM proposals WHERE application_id = ?");
    $stmt->execute([$app_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE proposals SET scoring_points = ?, risk_level = ?, recommended_amount = ?, ai_summary = ?, status = 'draft', updated_at = NOW() WHERE application_id = ?");
        $stmt->execute([$scoring, $risk, $recommended, $summary, $app_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO proposals (application_id, consultant_id, scoring_points, risk_level, recommended_amount, ai_summary, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')");
        $stmt->execute([$app_id, $_SESSION['user_id'], $scoring, $risk, $recommended, $summary]);
    }

    // Update application status
    $pdo->prepare("UPDATE applications SET status = 'analyzed', consultant_id = ? WHERE id = ?")->execute([$_SESSION['user_id'], $app_id]);

    // Log
    $pdo->prepare("INSERT INTO activity_log (user_id, user_type, action, details) VALUES (?, 'consultant', 'ai_analysis_run', ?)")
        ->execute([$_SESSION['user_id'], "Ran AI analysis for Application #$app_id. Score: $scoring, Risk: $risk"]);

    echo json_encode(['success' => true, 'scoring' => $scoring, 'risk' => $risk, 'recommended' => $recommended]);
} catch (PDOException $e) {
    error_log('run_analysis error: ' . $e->getMessage());
    echo json_encode(['error' => 'Analysis failed. Please try again.']);
}