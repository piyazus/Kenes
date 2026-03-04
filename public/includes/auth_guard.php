<?php
/**
 * Auth Guard — session check middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

/**
 * Require authentication. Optionally restrict to a specific user type.
 * @param string|null $type 'customer' or 'consultant' or null for any
 */
function requireAuth($type = null)
{
    global $base_url;
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $base_url . '/login.php');
        exit;
    }
    if ($type && ($_SESSION['user_type'] ?? '') !== $type) {
        header('Location: ' . $base_url . '/login.php');
        exit;
    }
}

/**
 * Generate or return existing CSRF token
 */
function csrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from POST data
 */
function verifyCsrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid CSRF token']));
    }
}

/**
 * Output a hidden CSRF input field
 */
function csrfField()
{
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}
?>