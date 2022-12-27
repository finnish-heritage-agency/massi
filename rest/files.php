<?php

/**
 * Edit and remove filedata in the DB
 */
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

if (isset($_POST["removeFiles"])) {
    if (isset($_POST["row_id"])) {
        $row_id = checkPost($_POST["row_id"]);
    }
    $file->setRowId($row_id);
    $message = $file->removeFiles();
} elseif (isset($_POST["makeReady"])) {
    if (isset($_POST["column"])) {
        $column = checkPost($_POST["column"]);
    }
    if (isset($_POST["status"])) {
        $status = checkPost($_POST["status"]);
    }
    $message = $file->changeFileColumnStatus($column, $status);
}

echo(json_encode($message));

