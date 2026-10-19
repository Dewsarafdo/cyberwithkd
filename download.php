<?php
// Sends a PDF as a download. Only PDFs of published items (or any PDF when the admin is logged in).
require 'config.php';
$r = q("SELECT f.*, e.published FROM files f JOIN entries e ON e.id=f.entry_id WHERE f.id=? AND f.kind='pdf'", [(int) ($_GET['id'] ?? 0)])->fetch();
$path = $r ? __DIR__ . '/uploads/' . basename($r['filename']) : '';
if (!$r || (!$r['published'] && empty($_SESSION['uid'])) || !is_file($path)) {
    http_response_code(404);
    exit('File not found');
}
session_write_close();
$n = $r['orig_name'] ?: 'document.pdf';
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="' . addcslashes(preg_replace('/[^\x20-\x7e]/', '_', $n), '"\\') . '"; filename*=UTF-8\'\'' . rawurlencode($n));
header('X-Content-Type-Options: nosniff');
while (ob_get_level())
    ob_end_clean();
readfile($path);
