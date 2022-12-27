<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;

if (isset($_POST["file"])) {
    $tmp_file = checkPost($_POST["file"]);
}
if (isset($_POST["folder"])) {
    $folder = checkPost($_POST["folder"]);
}
if (!$folder || !$tmp_file) {
    jsonError("Required arguments missing ");
    return;
}

$db = getConnect();
$file = new File($db, $folder, $tmp_file);

if (isset($_POST["newFile"])) {
    if (isset($_POST["row_id"])) {
        $row_id = checkPost($_POST["row_id"]);
    }
    if (!$row_id) {
        jsonError("Required arguments still missing");
        return;
    }
    $file->setRowId($row_id);
    $message = $file->saveFile();
}

echo(json_encode($message));

