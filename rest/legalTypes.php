<?php

/**
 * Haetaan lisenssit. Voidaan myös ns. muuttaa lisenssien status
 * 7.9.2020 changed licenses to legals...
 *
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = 1;
$db = getConnect();
$legal = new LegalType($db);

if (isset($_POST["getLegalTypes"])) {
    if (isset($_POST["active"])) {
        $active_status = 1;
    } else {
        $active_status = null;
    }
    $message = $legal->getAllLegalTypes($active_status);
} elseif (isset($_POST["changeStatus"]) && is_numeric($_POST["changeStatus"])) {
    $active = 0;
    if (isset($_POST["active"]) && is_numeric($_POST["active"])) {
        $active = $_POST["active"];
    }
//    $message = $legal->changeActiveStatus($_POST["changeStatus"], $active);
    //Just delete
    $message = $legal->deleteLegalType($_POST["changeStatus"]);
}
echo(json_encode($message));

