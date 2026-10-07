<?php
/**
 * login.php — Gate I. The archivist authentication gate.
 *
 * POST JSON: { "username": "...", "password": "..." }
 * Response:  { "ok": bool }
 *
 * INTENTIONALLY VULNERABLE: input is concatenated straight into the query.
 * The challenge filter (oracle_filter) only blocks the exact uppercase strings
 * 'UNION' and 'OR 1=1' — so ' or 1=1-- or ' or '1'='1 bypass it.
 */

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json');

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$username = oracle_filter((string) ($input['username'] ?? ''));
$password = oracle_filter((string) ($input['password'] ?? ''));

$ok = false;

if ($username !== '' || $password !== '') {
    try {
        $pdo = db();
        // Intentionally concatenated — the injection point.
        $sql = "SELECT * FROM archivists WHERE username = '$username' AND password = '$password'";
        $res = $pdo->query($sql);
        $ok  = ($res && $res->fetch() !== false);
    } catch (\Throwable $e) {
        $ok = false;
    }
}

echo json_encode(['ok' => $ok]);
