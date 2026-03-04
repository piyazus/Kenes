<?php
require_once __DIR__ . '/public/includes/db.php';
try {
    $stmt = $pdo->query("SHOW CREATE TABLE proposals");
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
