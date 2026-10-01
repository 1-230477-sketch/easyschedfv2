<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'db.php';

$pdo = db();
if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
    fwrite(STDERR, "EASYSCHED_DATABASE_URL must select PostgreSQL for this check.\n");
    exit(1);
}

$beforeRequests = (int) $pdo->query('SELECT COUNT(*) FROM schedule_requests')->fetchColumn();
$pdo->beginTransaction();
try {
    $pdo->exec('INSERT INTO schedule_requests (term_id, offering_id, instructor_id, room_id, day_of_week, slot_id) SELECT term_id, offering_id, instructor_id, room_id, day_of_week, slot_id FROM schedule_requests ORDER BY id LIMIT 1');
    $requestId = db_insert_id($pdo, 'schedule_requests');
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
$afterRequests = (int) $pdo->query('SELECT COUNT(*) FROM schedule_requests')->fetchColumn();
if ($requestId < 1 || $beforeRequests !== $afterRequests) {
    throw new RuntimeException('PostgreSQL generated-ID rollback check failed.');
}

$programCode = (string) $pdo->query('SELECT code FROM programs ORDER BY id LIMIT 1')->fetchColumn();
$caseInsensitiveUnique = false;
$pdo->beginTransaction();
try {
    $insert = $pdo->prepare('INSERT INTO programs (code, name) VALUES (?, ?)');
    $insert->execute([strtolower($programCode), 'PostgreSQL uniqueness probe']);
} catch (PDOException) {
    $caseInsensitiveUnique = true;
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
if (!$caseInsensitiveUnique) {
    throw new RuntimeException('PostgreSQL accepted a case-insensitive duplicate program code.');
}

echo "PostgreSQL generated IDs, rollback, and case-insensitive uniqueness: OK\n";
