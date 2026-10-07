<?php
/**
 * battle.php — stateless "warrior named" check via HMAC token.
 *
 * POST JSON: { "token": "<hex>" }
 * Response:  { "ok": bool }
 *
 * Used by the download endpoint so the graded environment stays stateless.
 */

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json');

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$token = (string) ($input['token'] ?? '');

echo json_encode(['ok' => ($token !== '' && hash_equals(download_token(), $token))]);
