<?php
/**
 * check.php — server-side gate validation.
 *
 * POST JSON: { "type": "gate|allegiance|lineage|name", "value": <mixed> }
 * Response:  { "ok": bool, "token"?: string, "message"?: string }
 *
 * The accepted answers are checked here (never in the front-end source).
 */

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json');

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$type  = $input['type']  ?? '';
$value = $input['value'] ?? '';

$ok      = false;
$message = '';
$token   = null;

switch ($type) {
    case 'allegiance':
        // Gate II: warrior #7 is Pandava.
        $ok = (strtolower(trim((string) $value)) === 'pandava');
        if (!$ok) {
            $message = 'The Oracle rejects that answer. Query deeper.';
        }
        break;

    case 'lineage':
        // Gate III: warrior #7's father is Arjuna.
        $ok = (strtolower(trim((string) $value)) === 'arjuna');
        if (!$ok) {
            $message = 'The Oracle does not confirm that lineage. Query deeper.';
        }
        break;

    case 'name':
        // Gate IV: the warrior is Iravan.
        $guess = strtolower(trim((string) $value));
        if (in_array($guess, ['iravan', 'iravat', 'iravaan'], true)) {
            $ok    = true;
            $token = download_token();
        } elseif (in_array($guess, ['barbarik', 'barbareek'], true)) {
            $message = 'That name belongs to another record in the archive. Look again.';
        } else {
            $message = 'The Oracle does not confirm that name. The evidence points elsewhere.';
        }
        break;

    default:
        $message = 'Unknown gate.';
}

echo json_encode(['ok' => $ok, 'token' => $token, 'message' => $message]);
