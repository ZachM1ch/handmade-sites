<?php
/**
 * Corkboard — optional server storage.
 *
 * When this file runs (PHP 7.4+), the board app saves boards and uploads into
 * ./data/ on your server. If PHP isn't available, the app quietly falls back to
 * saving in the visitor's browser instead.
 *
 * There is no login here. Anyone who can reach this folder can read and edit
 * the boards, so protect the folder (e.g. HTTP basic auth) if it is public.
 */
declare(strict_types=1);

const DATA_DIR   = __DIR__ . '/data';
const MAX_UPLOAD = 64 * 1024 * 1024;   // per file; PHP's own upload limits also apply
const MAX_BOARD  = 8 * 1024 * 1024;    // board JSON (PDF thumbnails live inside it)
const ID_RE      = '/^[A-Za-z0-9_-]{1,64}$/';

if (!function_exists('mb_substr')) { function mb_substr($s, $start, $len = null) { return substr($s, $start, $len); } }

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function out(array $d, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($d, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function fail(string $msg, int $code = 400): void { out(['ok' => false, 'error' => $msg], $code); }
function boardsDir(): string { return DATA_DIR . '/boards'; }
function filesDir(): string { return DATA_DIR . '/files'; }
function ensureDirs(): bool {
    foreach ([DATA_DIR, boardsDir(), filesDir()] as $d) if (!is_dir($d)) @mkdir($d, 0775, true);
    if (is_dir(DATA_DIR) && !file_exists(DATA_DIR . '/.htaccess')) {
        @file_put_contents(DATA_DIR . '/.htaccess', "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
    }
    return is_writable(boardsDir()) && is_writable(filesDir());
}
function needId(string $key = 'id'): string {
    $id = $_GET[$key] ?? '';
    if (!is_string($id) || !preg_match(ID_RE, $id)) fail('Invalid id');
    return $id;
}
/** POSTs must carry a custom header, which a cross-site form can't send (basic CSRF guard). */
function needPost(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') fail('POST required', 405);
    if (($_SERVER['HTTP_X_CORKBOARD'] ?? '') !== '1') fail('Missing X-Corkboard header', 403);
}
function writeAtomic(string $path, string $data): bool {
    $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $data, LOCK_EX) === false) return false;
    if (!@rename($tmp, $path)) { @unlink($tmp); return false; }
    return true;
}
function iniBytes(string $v): int {
    $v = trim($v); if ($v === '') return 0;
    $n = (int)$v; $u = strtolower(substr($v, -1));
    if ($u === 'g') $n *= 1 << 30; elseif ($u === 'm') $n *= 1 << 20; elseif ($u === 'k') $n *= 1 << 10;
    return $n;
}

$action = $_GET['action'] ?? '';

switch ($action) {

case 'ping':
    $lim = array_filter([iniBytes((string)ini_get('upload_max_filesize')), iniBytes((string)ini_get('post_max_size')), MAX_UPLOAD]);
    out(['ok' => true, 'storage' => 'server', 'writable' => ensureDirs(), 'maxUpload' => $lim ? min($lim) : MAX_UPLOAD]);

case 'list':
    ensureDirs();
    $boards = [];
    foreach (glob(boardsDir() . '/*.json') ?: [] as $f) {
        $b = json_decode((string)@file_get_contents($f), true);
        if (!is_array($b) || !isset($b['id'])) continue;
        $boards[] = [
            'id' => $b['id'], 'name' => (string)($b['name'] ?? 'Untitled'),
            'updated' => (int)($b['updated'] ?? filemtime($f) * 1000),
            'count' => is_array($b['items'] ?? null) ? count($b['items']) : 0,
        ];
    }
    out(['ok' => true, 'boards' => $boards]);

case 'load':
    $id = needId();
    $f = boardsDir() . "/$id.json";
    if (!is_file($f)) fail('Board not found', 404);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo '{"ok":true,"board":' . file_get_contents($f) . '}';
    exit;

case 'save':
    needPost();
    if (!ensureDirs()) fail('data/ folder is not writable', 500);
    $raw = file_get_contents('php://input', false, null, 0, MAX_BOARD + 1);
    if ($raw === false || strlen($raw) > MAX_BOARD) fail('Board too large', 413);
    $b = json_decode($raw, true);
    if (!is_array($b) || !isset($b['id']) || !is_string($b['id']) || !preg_match(ID_RE, $b['id'])) fail('Invalid board');
    if (!is_array($b['items'] ?? null) || !is_array($b['strings'] ?? null)) fail('Invalid board');
    $b['name'] = mb_substr((string)($b['name'] ?? 'Untitled'), 0, 120);
    $json = json_encode($b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if (!writeAtomic(boardsDir() . "/{$b['id']}.json", $json)) fail('Could not write board', 500);
    out(['ok' => true]);

case 'delete':
    needPost();
    $id = needId();
    @unlink(boardsDir() . "/$id.json");
    out(['ok' => true]);

case 'upload':
    needPost();
    if (!ensureDirs()) fail('data/ folder is not writable', 500);
    if (empty($_FILES['file'])) {
        fail('No file received — it may exceed the server\'s post_max_size (' . ini_get('post_max_size') . ')', 413);
    }
    $up = $_FILES['file'];
    $errs = [
        UPLOAD_ERR_INI_SIZE => 'File is larger than the server allows (upload_max_filesize = ' . ini_get('upload_max_filesize') . ')',
        UPLOAD_ERR_FORM_SIZE => 'File too large', UPLOAD_ERR_PARTIAL => 'Upload was interrupted',
        UPLOAD_ERR_NO_FILE => 'No file received', UPLOAD_ERR_NO_TMP_DIR => 'Server has no temp folder',
        UPLOAD_ERR_CANT_WRITE => 'Server could not write the file', UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension',
    ];
    if ($up['error'] !== UPLOAD_ERR_OK) fail($errs[$up['error']] ?? 'Upload failed', 400);
    if ($up['size'] > MAX_UPLOAD) fail('File too large', 413);
    $id = bin2hex(random_bytes(12));
    // Stored with a neutral extension so nothing uploaded can ever execute as a script.
    if (!move_uploaded_file($up['tmp_name'], filesDir() . "/$id.bin")) fail('Could not store file', 500);
    $type = '';
    if (function_exists('finfo_open')) { $fi = finfo_open(FILEINFO_MIME_TYPE); $type = (string)finfo_file($fi, filesDir() . "/$id.bin"); finfo_close($fi); }
    $client = (string)($up['type'] ?? '');
    if ($type === '' || $type === 'application/octet-stream' || $type === 'text/plain' && $client !== '' && strpos($client, 'text/') === 0) $type = $client ?: $type;
    $meta = ['name' => mb_substr(basename((string)$up['name']), 0, 200), 'type' => $type ?: 'application/octet-stream', 'size' => (int)$up['size'], 'created' => time()];
    file_put_contents(filesDir() . "/$id.json", json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
    out(['ok' => true, 'id' => $id]);

case 'file':
    $id = $_GET['id'] ?? '';
    if (!is_string($id) || !preg_match('/^[a-f0-9]{24}$/', $id)) fail('Invalid id');
    $path = filesDir() . "/$id.bin";
    if (!is_file($path)) fail('File not found', 404);
    $meta = json_decode((string)@file_get_contents(filesDir() . "/$id.json"), true) ?: [];
    $type = (string)($meta['type'] ?? 'application/octet-stream');
    if (!preg_match('#^[\w.+-]+/[\w.+-]+$#', $type)) $type = 'application/octet-stream';
    $name = (string)($meta['name'] ?? 'file');
    // Types a browser could run script from are never rendered inline from this origin.
    $risky = (bool)preg_match('#(html|xml|svg|javascript|ecmascript|x-sh|x-php)#i', $type);
    $inline = !$risky && empty($_GET['dl']);
    $etag = '"' . $id . '"';
    if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) { http_response_code(304); exit; }
    header('Content-Type: ' . $type);
    header('Content-Length: ' . filesize($path));
    header('ETag: ' . $etag);
    header('Cache-Control: private, max-age=31536000, immutable');
    $ascii = preg_replace('/[^\x20-\x7E]|["\\\\]/', '_', $name);
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($name));
    if ($risky) header("Content-Security-Policy: sandbox; default-src 'none'");
    readfile($path);
    exit;

case 'gc':
    // Remove uploads that no board refers to any more (older than an hour, so undo still works).
    needPost();
    ensureDirs();
    $live = [];
    foreach (glob(boardsDir() . '/*.json') ?: [] as $f) {
        if (preg_match_all('/srv:([a-f0-9]{24})/', (string)file_get_contents($f), $m)) foreach ($m[1] as $x) $live[$x] = true;
    }
    $removed = 0;
    foreach (glob(filesDir() . '/*.bin') ?: [] as $f) {
        $id = basename($f, '.bin');
        if (!isset($live[$id]) && filemtime($f) < time() - 3600) { @unlink($f); @unlink(filesDir() . "/$id.json"); $removed++; }
    }
    out(['ok' => true, 'removed' => $removed]);

case 'unfurl':
    if (($_SERVER['HTTP_X_CORKBOARD'] ?? '') !== '1') fail('Missing X-Corkboard header', 403);
    $url = (string)($_GET['url'] ?? '');
    // Failures still answer 200 so the browser can fall back to other preview sources quietly.
    try { out(['ok' => true, 'meta' => unfurl($url)]); }
    catch (Throwable $e) { out(['ok' => true, 'meta' => new stdClass(), 'note' => $e->getMessage()]); }

default:
    fail('Unknown action', 404);
}

/* ------------------------------------------------------------------ link previews */

/** Refuse to fetch anything that resolves to a private, loopback or reserved address. */
function publicIps(string $host): array {
    $host = trim($host, '[]');
    if (filter_var($host, FILTER_VALIDATE_IP)) $ips = [$host];
    else {
        $ips = @gethostbynamel($host) ?: [];
        if (function_exists('dns_get_record')) foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $r) if (!empty($r['ipv6'])) $ips[] = $r['ipv6'];
    }
    if (!$ips) throw new RuntimeException('Could not resolve host');
    foreach ($ips as $ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) throw new RuntimeException('Host not allowed');
    }
    return $ips;
}
function checkUrl(string $url): array {
    $p = parse_url($url);
    if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true) || empty($p['host'])) throw new RuntimeException('Invalid URL');
    if (isset($p['user']) || isset($p['pass'])) throw new RuntimeException('Invalid URL');
    $port = $p['port'] ?? (strtolower($p['scheme']) === 'https' ? 443 : 80);
    if (!in_array($port, [80, 443, 8080, 8443], true)) throw new RuntimeException('Port not allowed');
    return [$p, publicIps($p['host']), $port];
}
function absUrl(string $rel, string $base): string {
    $rel = trim(html_entity_decode($rel, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($rel === '') return '';
    if (preg_match('#^https?://#i', $rel)) return $rel;
    $b = parse_url($base); $origin = $b['scheme'] . '://' . $b['host'] . (isset($b['port']) ? ':' . $b['port'] : '');
    if (strpos($rel, '//') === 0) return $b['scheme'] . ':' . $rel;
    if ($rel[0] === '/') return $origin . $rel;
    if (preg_match('#^[a-z][\w+.-]*:#i', $rel)) return ''; // data:, javascript:, etc.
    $dir = preg_replace('#/[^/]*$#', '/', $b['path'] ?? '/');
    return $origin . $dir . $rel;
}
function httpGet(string $url): array {
    $ua = 'Mozilla/5.0 (compatible; CorkboardPreview/1.0; +link-preview)';
    for ($hop = 0; $hop < 5; $hop++) {
        [$p, $ips, $port] = checkUrl($url);
        $status = 0; $headers = []; $body = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => $ua, CURLOPT_ENCODING => '',
                CURLOPT_HTTPHEADER => ['Accept: text/html,application/xhtml+xml;q=0.9,*/*;q=0.5', 'Accept-Language: en'],
                CURLOPT_RESOLVE => [trim($p['host'], '[]') . ":$port:" . $ips[0]], // pin to the address we vetted
                CURLOPT_HEADERFUNCTION => function ($c, $h) use (&$headers) { $kv = explode(':', $h, 2); if (count($kv) === 2) $headers[strtolower(trim($kv[0]))] = trim($kv[1]); return strlen($h); },
                CURLOPT_WRITEFUNCTION => function ($c, $chunk) use (&$body) { $body .= $chunk; return strlen($body) > 800000 ? 0 : strlen($chunk); },
            ]);
            if (defined('CURLOPT_PROTOCOLS')) curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen')) {
            $ctx = stream_context_create(['http' => ['method' => 'GET', 'follow_location' => 0, 'timeout' => 8, 'ignore_errors' => true,
                'header' => "User-Agent: $ua\r\nAccept: text/html,*/*;q=0.5\r\nAccept-Language: en\r\n"]]);
            $body = (string)@file_get_contents($url, false, $ctx, 0, 800000);
            $raw = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?: []) : ($http_response_header ?? []);
            foreach ($raw as $h) {
                if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) { $status = (int)$m[1]; $headers = []; continue; }
                $kv = explode(':', $h, 2); if (count($kv) === 2) $headers[strtolower(trim($kv[0]))] = trim($kv[1]);
            }
        } else {
            throw new RuntimeException('Server cannot make outbound requests');
        }
        if ($status >= 300 && $status < 400 && !empty($headers['location'])) { $url = absUrl($headers['location'], $url); if ($url === '') break; continue; }
        return [$status, $headers, $body, $url];
    }
    throw new RuntimeException('Too many redirects');
}
function unfurl(string $url): array {
    [$status, $headers, $html, $final] = httpGet($url);
    if ($status >= 400 || $status === 0) throw new RuntimeException("Page returned $status");
    $ctype = $headers['content-type'] ?? '';
    if (preg_match('#^image/#i', $ctype)) return ['image' => $final, 'title' => basename(parse_url($final, PHP_URL_PATH) ?: '')];
    if ($ctype !== '' && !preg_match('#html|xml#i', $ctype)) return ['title' => basename(parse_url($final, PHP_URL_PATH) ?: '')];

    $charset = '';
    if (preg_match('/charset=([\w-]+)/i', $ctype, $m)) $charset = $m[1];
    elseif (preg_match('/<meta[^>]+charset=["\']?([\w-]+)/i', $html, $m)) $charset = $m[1];
    if ($charset && strcasecmp($charset, 'utf-8') !== 0 && function_exists('mb_convert_encoding')) $html = @mb_convert_encoding($html, 'UTF-8', $charset) ?: $html;

    $meta = [];
    if (preg_match_all('/<meta\b[^>]*>/i', $html, $tags)) {
        foreach ($tags[0] as $tag) {
            $a = attrs($tag);
            $k = strtolower($a['property'] ?? $a['name'] ?? $a['itemprop'] ?? '');
            if ($k !== '' && isset($a['content']) && !isset($meta[$k])) $meta[$k] = $a['content'];
        }
    }
    $icon = '';
    if (preg_match_all('/<link\b[^>]*>/i', $html, $links)) {
        foreach ($links[0] as $tag) {
            $a = attrs($tag);
            if (preg_match('/(^|\s)(icon|apple-touch-icon)(\s|$)/i', $a['rel'] ?? '') && !empty($a['href'])) { $icon = absUrl($a['href'], $final); if (stripos($a['rel'], 'apple') !== false) break; }
        }
    }
    $title = $meta['og:title'] ?? $meta['twitter:title'] ?? '';
    if ($title === '' && preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m)) $title = $m[1];
    $clean = fn($s) => mb_substr(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string)$s), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 0, 400);
    $img = $meta['og:image'] ?? $meta['og:image:url'] ?? $meta['og:image:secure_url'] ?? $meta['twitter:image'] ?? $meta['twitter:image:src'] ?? $meta['image'] ?? '';
    return array_filter([
        'title' => $clean($title),
        'description' => $clean($meta['og:description'] ?? $meta['twitter:description'] ?? $meta['description'] ?? ''),
        'image' => $img !== '' ? absUrl($img, $final) : '',
        'siteName' => $clean($meta['og:site_name'] ?? ''),
        'favicon' => $icon,
    ]);
}
function attrs(string $tag): array {
    $out = [];
    if (preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/', $tag, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $v = ($x[2] ?? '') !== '' ? $x[2] : (($x[3] ?? '') !== '' ? $x[3] : ($x[4] ?? ''));
            $out[strtolower($x[1])] = html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    return $out;
}
