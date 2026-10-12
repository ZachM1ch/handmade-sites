<?php
/**
 * tables.php - saves and serves tables JSON files for the Mausritter tools
 * (hexcrawl-generator.html, site-generator.html, mouse-generator.html).
 *
 * Put this file in the same folder as the generators. Uploaded tables are
 * saved in that folder as "<name>-tables.json", and the generators list the
 * "*-tables.json" files they can use:
 *  - hexcrawl-generator.html (no "for" parameter) lists hexcrawl tables only.
 *  - the other tools (?for=all) also list files that only hold their own
 *    sections (site, treasure, creatures, npc, seeds, mouse, hirelings), and
 *    may upload them. (?for=sites is the older name for the same thing.)
 *
 * Setup: the PHP user (often www-data) must be able to write to this folder.
 * If your site is public, set UPLOAD_KEY below so only people with the
 * password can add or delete files.
 */

// Leave empty for no password. Anyone who can open the page can then add and delete tables.
const UPLOAD_KEY = '';
// Largest file accepted, in bytes.
const MAX_BYTES = 1048576;
// These files can be replaced by an upload but not deleted from the page.
const PROTECTED_FILES = array('mausritter-tables.json');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond($code, $data)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Names the generator will list, read, replace or delete: letters, digits, . _ - and ending in -tables.json
function valid_name($name)
{
    return is_string($name) && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,80}-tables\.json$/', $name) === 1;
}

// Turns whatever file name was uploaded into a safe "<name>-tables.json".
function clean_name($raw)
{
    $n = basename(str_replace('\\', '/', (string) $raw));
    $n = preg_replace('/\.json$/i', '', $n);
    $n = preg_replace('/[^A-Za-z0-9._-]+/', '-', $n);
    $n = preg_replace('/\.{2,}/', '.', $n);
    $n = trim($n, '.-_');
    $n = preg_replace('/-?tables$/i', '', $n);
    $n = trim($n, '.-_');
    $n = substr($n, 0, 60);
    $n = trim($n, '.-_');
    if ($n === '') {
        $n = 'my';
    }
    return $n . '-tables.json';
}

// A light structural check. The page does the full validation before uploading.
function looks_like_tables($d)
{
    if (!is_array($d)) {
        return false;
    }
    foreach (array('map', 'landmarks', 'settlement') as $k) {
        if (!isset($d[$k]) || !is_array($d[$k])) {
            return false;
        }
    }
    foreach (array('hexType', 'details', 'hooks') as $k) {
        if (!isset($d[$k]) || !is_array($d[$k]) || count($d[$k]) === 0) {
            return false;
        }
    }
    return true;
}

// Extra tables: a file holding at least one of the sections the site and mouse generators add.
function looks_like_sites($d)
{
    if (!is_array($d)) {
        return false;
    }
    foreach (array('site', 'treasure', 'npc', 'mouse', 'hirelings') as $k) {
        if (isset($d[$k]) && is_array($d[$k]) && count($d[$k]) > 0) {
            return true;
        }
    }
    foreach (array('creatures', 'seeds') as $k) {
        if (isset($d[$k]) && is_array($d[$k]) && count($d[$k]) > 0) {
            return true;
        }
    }
    return false;
}

// 'hexcrawl' if the hexcrawl generator can use it, 'sites' if only the site generator can, or '' for neither.
function tables_kind($d)
{
    if (looks_like_tables($d)) {
        return 'hexcrawl';
    }
    return looks_like_sites($d) ? 'sites' : '';
}

function require_page_request()
{
    $h = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
    if ($h !== 'hexcrawl') {
        respond(400, array('error' => 'This request did not come from the generator page.'));
    }
}

function require_key()
{
    if (UPLOAD_KEY === '') {
        return;
    }
    $k = isset($_SERVER['HTTP_X_TABLES_KEY']) ? $_SERVER['HTTP_X_TABLES_KEY'] : '';
    if (!is_string($k) || !hash_equals(UPLOAD_KEY, $k)) {
        respond(401, array('error' => 'The upload password is missing or wrong.'));
    }
}

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$name = isset($_GET['name']) ? $_GET['name'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$forSites = isset($_GET['for']) && ($_GET['for'] === 'sites' || $_GET['for'] === 'all');

if ($method === 'GET') {
    // Return one file.
    if ($name !== null) {
        $path = __DIR__ . '/' . $name;
        if (!valid_name($name) || !is_file($path)) {
            respond(404, array('error' => 'No such tables file.'));
        }
        echo file_get_contents($path);
        exit;
    }
    // List the tables files in this folder.
    $list = array();
    $files = glob(__DIR__ . '/*-tables.json');
    if ($files === false) {
        $files = array();
    }
    sort($files);
    foreach (array_slice($files, 0, 200) as $f) {
        $base = basename($f);
        if (!valid_name($base) || !is_file($f) || filesize($f) > MAX_BYTES) {
            continue;
        }
        $d = json_decode((string) file_get_contents($f), true);
        $kind = tables_kind($d);
        if ($kind === '' || ($kind === 'sites' && !$forSites)) {
            continue;
        }
        $list[] = array(
            'name' => $base,
            'title' => (isset($d['title']) && is_string($d['title'])) ? $d['title'] : '',
            'kind' => $kind,
            'modified' => filemtime($f),
        );
    }
    respond(200, array(
        'tables' => $list,
        'canSave' => is_writable(__DIR__),
        'needsKey' => UPLOAD_KEY !== '',
        'protected' => PROTECTED_FILES,
    ));
}

if ($method === 'POST') {
    require_page_request();
    require_key();

    // Delete one file.
    if ($action === 'delete') {
        $path = __DIR__ . '/' . (string) $name;
        if (!valid_name($name) || !is_file($path)) {
            respond(404, array('error' => 'No such tables file.'));
        }
        if (in_array($name, PROTECTED_FILES, true)) {
            respond(403, array('error' => $name . ' is protected and cannot be deleted from the page.'));
        }
        if (!@unlink($path)) {
            respond(500, array('error' => 'The server could not delete that file.'));
        }
        respond(200, array('deleted' => $name));
    }

    // Save an uploaded file: the request body is the JSON text.
    if (!is_writable(__DIR__)) {
        respond(500, array('error' => 'The server folder is not writable.'));
    }
    $body = file_get_contents('php://input', false, null, 0, MAX_BYTES + 1);
    if ($body === false || $body === '') {
        respond(400, array('error' => 'The file was empty.'));
    }
    if (strlen($body) > MAX_BYTES) {
        respond(413, array('error' => 'The file is larger than ' . (int) (MAX_BYTES / 1024) . ' KB.'));
    }
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respond(400, array('error' => 'That file is not valid JSON.'));
    }
    $kind = tables_kind($data);
    if ($kind === '' || ($kind === 'sites' && !$forSites)) {
        respond(400, array('error' => $forSites ? 'That JSON does not look like a tables file.' : 'That JSON does not look like a hexcrawl tables file.'));
    }

    $final = clean_name($name === null ? 'my' : $name);
    $target = __DIR__ . '/' . $final;
    $replaced = is_file($target);
    $tmp = tempnam(__DIR__, '.upload');
    if ($tmp === false || file_put_contents($tmp, $body, LOCK_EX) === false) {
        if ($tmp !== false) {
            @unlink($tmp);
        }
        respond(500, array('error' => 'The server could not write the file.'));
    }
    @chmod($tmp, 0644);
    if (!@rename($tmp, $target)) {
        @unlink($tmp);
        respond(500, array('error' => 'The server could not save the file.'));
    }
    respond(200, array('name' => $final, 'replaced' => $replaced));
}

respond(405, array('error' => 'Method not allowed.'));
