<?php
require_once 'db.php';

try {
    $sql = file_get_contents('update_schema.sql');
    $pdo->exec($sql);
    echo "Database schema updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?>