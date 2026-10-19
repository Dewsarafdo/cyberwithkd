<?php
// ---- EDIT THESE ----
define('DB', ['host' => (getenv('DB_HOST') ?: '127.0.0.1'), 'port' => (int) (getenv('DB_PORT') ?: 3306), 'name' => (getenv('DB_NAME') ?: 'cyberfolio'), 'user' => (getenv('DB_USER') ?: 'root'), 'pass' => (getenv('DB_PASS') ?: '@Kdboss2006')]);
// Set these environment variables in your hosting control panel. Never commit secrets.
define('TURNSTILE_SITE_KEY', getenv('TURNSTILE_SITE_KEY') ?: ''); // Cloudflare Turnstile public site key
define('TURNSTILE_SECRET_KEY', getenv('TURNSTILE_SECRET_KEY') ?: ''); // Keep secret key private; set on server

define('DEBUG', getenv('APP_DEBUG') === '1'); // enable temporarily for local debugging only
// To add a new section to the site AND the admin panel, add one line here.
const TYPES = [
    'project' => ['label' => 'Projects', 'fields' => ['title', 'summary', 'body', 'category', 'images', 'videos', 'pdfs', 'tags', 'link']],
    'lesson' => ['label' => 'Cyber lessons', 'fields' => ['title', 'summary', 'body', 'category', 'images', 'videos', 'pdfs', 'tags', 'level']],
    'education' => ['label' => 'Education', 'hide' => true, 'fields' => ['title', 'summary', 'body', 'category', 'images', 'pdfs', 'period']],
    'cert' => ['label' => 'Certifications', 'hide' => true, 'fields' => ['title', 'summary', 'body', 'category', 'images', 'pdfs', 'link', 'period']],
];
if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
if (!empty($_SERVER['HTTPS']))
    ini_set('session.cookie_secure', 1);
session_start();
$csp = "default-src 'self'; img-src 'self' data:; media-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'";
if (TURNSTILE_SITE_KEY)
    $csp .= "; script-src 'self' https://challenges.cloudflare.com; frame-src https://challenges.cloudflare.com; connect-src 'self' https://challenges.cloudflare.com";
header('Content-Security-Policy: ' . $csp);
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cache-Control: no-store, max-age=0');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
function e($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
function first_type()
{
    foreach (TYPES as $k => $v)
        return $k;
}
if (!defined('SETUP')) {
    try {
        $pdo = new PDO('mysql:host=' . DB['host'] . ';port=' . DB['port'] . ';dbname=' . DB['name'] . ';charset=utf8mb4', DB['user'], DB['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch (PDOException $x) {
        http_response_code(500);
        die('<h2>Database not connected</h2><p>Open <a href="check.php">check.php</a> to find and fix the problem.</p>' . (DEBUG ? '<p>' . e($x->getMessage()) . '</p>' : ''));
    }
    try {
        migrate($pdo); // adds categories and the files table to an older database, once
    } catch (PDOException $x) {
        http_response_code(500);
        die('<h2>The database could not be updated</h2><p>Open <a href="check.php">check.php</a> and click the button, or check that the MySQL user may ALTER and CREATE tables.</p>' . (DEBUG ? '<p>' . e($x->getMessage()) . '</p>' : ''));
    }
}
function q($sql, $a = [])
{
    global $pdo;
    $s = $pdo->prepare($sql);
    $s->execute($a);
    return $s;
}
function setting($k)
{
    static $c;
    if ($c === null)
        $c = array_column(q('SELECT k,v FROM settings')->fetchAll(), 'v', 'k');
    return isset($c[$k]) ? $c[$k] : '';
}
function csrf()
{
    if (empty($_SESSION['t']))
        $_SESSION['t'] = bin2hex(random_bytes(16));
    return $_SESSION['t'];
}
function field()
{
    return '<input type="hidden" name="_t" value="' . csrf() . '">';
}
function check()
{
    if (!hash_equals(csrf(), $_POST['_t'] ?? ''))
        die('Invalid token');
}
function slugify($s)
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-') . '-' . bin2hex(random_bytes(2));
}
function cut($s, $n)
{
    return function_exists('mb_substr') ? mb_substr($s, 0, $n, 'UTF-8') : substr($s, 0, $n);
}
// ---- Database upgrade (safe to run many times) ----
function migrate($db)
{
    $schema = (int) $db->query("SELECT v FROM settings WHERE k='schema'")->fetchColumn();
    if ($schema < 2 && !$db->query("SHOW COLUMNS FROM entries LIKE 'category'")->fetch())
        $db->exec("ALTER TABLE entries ADD COLUMN category VARCHAR(60) NOT NULL DEFAULT '' AFTER summary");
    $db->exec("CREATE TABLE IF NOT EXISTS files(id INT AUTO_INCREMENT PRIMARY KEY, entry_id INT NOT NULL, kind VARCHAR(10) NOT NULL, filename VARCHAR(120) NOT NULL, orig_name VARCHAR(160) DEFAULT '', size INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY(entry_id,kind)) CHARACTER SET utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS client_users(id INT AUTO_INCREMENT PRIMARY KEY,email VARCHAR(190) NOT NULL UNIQUE,display_name VARCHAR(100) NOT NULL,pass VARCHAR(255) NOT NULL,verified_at DATETIME NULL,verify_token_hash CHAR(64) NULL,verify_expires DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,KEY(verify_token_hash)) CHARACTER SET utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS auth_attempts(id INT AUTO_INCREMENT PRIMARY KEY,ip_hash CHAR(64) NOT NULL,attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,KEY(ip_hash,attempted_at)) CHARACTER SET utf8mb4");
    // old single cover images become the first image of the new list
    $db->exec("INSERT INTO files(entry_id,kind,filename) SELECT e.id,'image',e.image FROM entries e WHERE e.image<>'' AND NOT EXISTS(SELECT 1 FROM files f WHERE f.entry_id=e.id AND f.filename=e.image)");
    $db->prepare('REPLACE INTO settings VALUES(?,?)')->execute(['schema', '2']);
}
// ---- Files: many images and PDFs per item ----
const MAX_IMG = 5000000;  // 5 MB per image
const MAX_PDF = 25000000; // 25 MB per PDF
const MAX_VIDEO = 50000000; // 50 MB per short video; use a video CDN for larger media
function files_in($key)
{
    $out = [];
    if (empty($_FILES[$key]))
        return $out;
    $f = $_FILES[$key];
    if (!is_array($f['name']))
        return [$f];
    foreach ($f['name'] as $i => $n)
        $out[] = ['name' => $n, 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    return $out;
}
function is_pdf($path)
{
    if (@file_get_contents($path, false, null, 0, 5) !== '%PDF-')
        return false;
    if (class_exists('finfo') && (new finfo(FILEINFO_MIME_TYPE))->file($path) !== 'application/pdf')
        return false;
    return true;
}
// Saves every file picked in <input name="$key[]" multiple>. Returns [saved, skipped, reasons].
function save_files($id, $key, $kind)
{
    $ok = 0;
    $why = [];
    foreach (files_in($key) as $f) {
        if ($f['error'] === UPLOAD_ERR_NO_FILE || $f['tmp_name'] === '')
            continue;
        $nm = cut(basename(str_replace('\\', '/', (string) $f['name'])), 60);
        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
            $why[] = "$nm is bigger than the PHP limit (upload_max_filesize is " . ini_get('upload_max_filesize') . ').';
            continue;
        }
        if ($f['error'] !== 0 || !is_uploaded_file($f['tmp_name'])) {
            $why[] = "$nm could not be uploaded (error " . (int) $f['error'] . ').';
            continue;
        }
        if ($kind === 'image') {
            $info = @getimagesize($f['tmp_name']);
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
            if (!$ext) {
                $why[] = "$nm is not a JPG, PNG or WebP image.";
                continue;
            }
            if ($f['size'] > MAX_IMG) {
                $why[] = "$nm is over " . (MAX_IMG / 1000000) . ' MB.';
                continue;
            }
        } elseif ($kind === 'video') {
            $mime = class_exists('finfo') ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : '';
            $ext = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/ogg' => 'ogv'][$mime] ?? null;
            if (!$ext) {
                $why[] = "$nm is not an MP4, WebM or Ogg video.";
                continue;
            }
            if ($f['size'] > MAX_VIDEO) {
                $why[] = "$nm is over " . (MAX_VIDEO / 1000000) . ' MB.';
                continue;
            }
        } else {
            if (!is_pdf($f['tmp_name'])) {
                $why[] = "$nm is not a real PDF file.";
                continue;
            }
            if ($f['size'] > MAX_PDF) {
                $why[] = "$nm is over " . (MAX_PDF / 1000000) . ' MB.';
                continue;
            }
            $ext = 'pdf';
        }
        $fn = bin2hex(random_bytes(8)) . ".$ext";
        if (!move_uploaded_file($f['tmp_name'], __DIR__ . "/uploads/$fn")) {
            $why[] = "$nm could not be saved (is the uploads folder writable?).";
            continue;
        }
        $orig = preg_replace('/[^\w\s.\-()\[\]]+/u', '_', basename(str_replace('\\', '/', (string) $f['name'])));
        $orig = cut($orig ?: 'file', 150);
        if ($kind === 'pdf' && !preg_match('/\.pdf$/i', $orig))
            $orig .= '.pdf';
        if ($kind === 'video' && !preg_match('/\.(mp4|webm|ogv)$/i', $orig))
            $orig .= '.' . $ext;
        q('INSERT INTO files(entry_id,kind,filename,orig_name,size) VALUES(?,?,?,?,?)', [$id, $kind, $fn, $orig, (int) $f['size']]);
        $ok++;
    }
    return [$ok, count($why), $why];
}
function remove_file($row)
{
    @unlink(__DIR__ . '/uploads/' . basename($row['filename']));
    q('DELETE FROM files WHERE id=?', [$row['id']]);
}
// Keeps entries.image (the cover shown on cards) pointing at a real image of the item.
function fix_cover($id)
{
    $e = q('SELECT image FROM entries WHERE id=?', [$id])->fetch();
    if ($e && $e['image'] !== '' && q("SELECT COUNT(*) FROM files WHERE entry_id=? AND kind='image' AND filename=?", [$id, $e['image']])->fetchColumn() > 0)
        return;
    $f = q("SELECT filename FROM files WHERE entry_id=? AND kind='image' ORDER BY id LIMIT 1", [$id])->fetchColumn();
    q('UPDATE entries SET image=? WHERE id=?', [$f ?: '', $id]);
}
// files_for([1,2,3]) => [1=>['image'=>[...],'pdf'=>[...]], ...] in one query
function files_for($ids)
{
    $ids = array_values(array_filter(array_map('intval', (array) $ids)));
    $out = [];
    foreach ($ids as $i)
        $out[$i] = ['image' => [], 'pdf' => [], 'video' => []];
    if (!$ids)
        return $out;
    foreach (q('SELECT * FROM files WHERE entry_id IN (' . implode(',', $ids) . ') ORDER BY id')->fetchAll() as $f) {
        $kind = in_array($f['kind'], ['image', 'pdf', 'video'], true) ? $f['kind'] : 'image';
        $entryId = (int) $f['entry_id'];
        if (isset($out[$entryId])) {
            $out[$entryId][$kind][] = $f;
        }
    }
    return $out;
}
function fsize($n)
{
    return $n >= 1048576 ? round($n / 1048576, 1) . ' MB' : max(1, round($n / 1024)) . ' KB';
}
