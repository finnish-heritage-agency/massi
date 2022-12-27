<?php

/**
 * Tarkistetaan voidaanko ko hakemistoa siirtää/poistaa
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;
$db = getConnect();
$collection = new Collection($db);
$folder = $_POST["folder"];
if (isset($_POST["canRemove"])) {
    $message = $collection->canRemove($folder);
}

echo(json_encode($message));

