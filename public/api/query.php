<?php
/**
 * query.php — Gates II–IV. The war-registry search.
 *
 * POST JSON: { "q": "<player input>" }
 * Response:  { "rows": [[col1, col2], ...] }
 *
 * INTENTIONALLY VULNERABLE: the player's input is concatenated into
 *   SELECT name, side FROM kshetra_rakshak WHERE name = '<q>' LIMIT 20
 * so a UNION-based injection returns real rows from the database.
 * The weak filter only blocks the exact uppercase 'UNION' / 'OR 1=1'.
 */

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json');

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$q = oracle_filter((string) ($input['q'] ?? ''));

$rows = [];

if ($q !== '') {
    try {
        $pdo = db();
        // Intentionally concatenated — the injection point.
        $sql = "SELECT name, side FROM kshetra_rakshak WHERE name = '$q' LIMIT 20";
        $res = $pdo->query($sql);
        if ($res) {
            while (($row = $res->fetch(PDO::FETCH_NUM)) !== false) {
                $rows[] = $row;
            }
        }
    } catch (\Throwable $e) {
        // Mirror the original: a broken query just yields no rows.
        $rows = [];
    }
}

echo json_encode(['rows' => $rows]);
