<?php
require_once __DIR__ . '/includes/db.php';
try {
    // Drop foreign keys if any
    $pdo->exec("ALTER TABLE proposals DROP FOREIGN KEY proposals_ibfk_2");
} catch (Exception $e) {
}

try {
    $pdo->exec("ALTER TABLE proposals DROP FOREIGN KEY proposals_ibfk_1");
} catch (Exception $e) {
}

try {
    // If the constraint name was different
    $stmt = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'proposals' AND COLUMN_NAME = 'consultant_id' AND TABLE_SCHEMA = DATABASE()");
    while ($row = $stmt->fetch()) {
        $pdo->exec("ALTER TABLE proposals DROP FOREIGN KEY " . $row['CONSTRAINT_NAME']);
    }
} catch (Exception $e) {
}

try {
    $pdo->exec("ALTER TABLE proposals ADD CONSTRAINT fk_proposals_consultant FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE SET NULL");
    echo "Fixed foreign key on proposals.\n";
} catch (Exception $e) {
    echo "Error fixing FK: " . $e->getMessage() . "\n";
}
