<?php
$file = basename($_GET['file']);
$path = $_SERVER['DOCUMENT_ROOT'] . "/assets/downloadable/" . $file;

if (file_exists($path)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$file.'"');
    readfile($path);
} else {
    echo "File not found.";
}
?>
