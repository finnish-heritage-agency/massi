<?php

/**
 * Lisätään uusi oikeus
 *
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;

if (isset($_POST["internal_name"])) {
    $internal_name = checkPost($_POST["internal_name"]);
}

if (isset($_POST["type_name"])) {
    $type_name = checkPost($_POST["type_name"]);
}
if (!$type_name || !$internal_name) {
    jsonError("Required arguments missing");
    return;
}
$db = getConnect();
$legal = new LegalType($db, 0, $type_name, $internal_name, 1);

if (isset($_POST["add"])) {
    $message = $legal->addLegalType();
}
echo(json_encode($message));

