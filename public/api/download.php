<?php
/**
 * download.php — serve the sealed banner.
 *
 * GET /api/download.php?token=<hex>
 *
 * The flag is appended after the PNG IEND chunk (EOF steganography),
 * exactly like the original challenge. Requires a valid token proving
 * the warrior was named.
 */

require_once __DIR__ . '/../lib/db.php';

$token = (string) ($_GET['token'] ?? '');

if (!hash_equals(download_token(), $token)) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/plain');
    echo 'The banner has not yet been earned. Name the warrior first.';
    exit;
}

$file = dirname(__DIR__, 2) . '/assets/kali_banner.png';

if (!file_exists($file)) {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/plain');
    echo '[Oracle] kali_banner.png not found.';
    exit;
}

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="kali_banner.png"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-cache, no-store');
readfile($file);
exit;
