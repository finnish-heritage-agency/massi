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

if (isset($_POST["license"])) {
    $license_name = checkPost($_POST["license"]);
}
if (!$internal_name || !$license_name) {
    jsonError("Required arguments missing");
    return;
}
$db = getConnect();
$legal = new Legal($db, 0, $license_name, $internal_name, 1);

if (isset($_POST["add"])) {
    $message = $legal->addLegal();
}
echo(json_encode($message));

