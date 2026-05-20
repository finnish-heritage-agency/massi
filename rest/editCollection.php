<?php

/**
 * Muokataan kokoelman rivien statuksia & tallennetaan objektinIDt
 * ALTER TABLE `listan_rivit` ADD `objektin_id` INT(10) NULL DEFAULT NULL AFTER `kokoelmatunnus`;
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
header("Access-Control-Allow-Methods: POST");
$db = getConnect();
$collection = new Collection($db);
if (isset($_POST["changeCollectionRowStatus"])) {
    $rivi_id = checkPost($_POST["changeCollectionRowStatus"]);
    if (isset($_POST["progress"])) {
        $progress = checkPost($_POST["progress"]);
    }
    if (!$rivi_id || !$progress) {
        jsonError("Required arguments missing");
        return;
    }
    $message = $collection->changeCollectionRowStatus($rivi_id, $progress);
} elseif (isset($_POST["saveCollectionId"])) {
    if (isset($_POST["object_id"])) {
        $object_id = checkPost($_POST["object_id"]);
    }
    if (isset($_POST["object_name"])) {
        $object_name = checkPost($_POST["object_name"]);
    }
    if (!$object_name || !$object_id) {
        jsonError("Required arguments missing");
        return;
    }
    $message = $collection->saveObjectId($object_name, $object_id);
} elseif (isset($_POST["saveCollectionFileCount"])) {
    if (isset($_POST["count"])) {
        $count = checkNumber($_POST["count"]);
    }
    if (isset($_POST["object_name"])) {
        $object_name = checkPost($_POST["object_name"]);
    }
    if (!$object_name || !$count) {
        jsonError("Required arguments missing");
        return;
    }
    $message = $collection->saveFileCount($object_name, $count);
}

echo(json_encode($message));
