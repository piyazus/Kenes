<?php
/**
 * i18n Loader — Kenes Platform
 * Loads language translations and provides __() helper.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine language: GET param > session > default 'en'
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';

// Validate language
$supported_langs = ['en', 'kz', 'ru'];
if (!in_array($lang, $supported_langs)) {
    $lang = 'en';
}

// Persist to session
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $lang;
}

// Load translation array
$lang_file = __DIR__ . "/../lang/{$lang}.php";
if (file_exists($lang_file)) {
    $t = require $lang_file;
} else {
    $t = require __DIR__ . "/../lang/en.php";
}

/**
 * Translate a key. Returns the key itself if no translation found.
 */
function __($key)
{
    global $t;
    return $t[$key] ?? $key;
}
?>