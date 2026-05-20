<?php

/**
 * Haetaan tekijöiden nimet. Voidaan myös ns. poistaa tekijä
 *
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;
$db = getConnect();
$artist = new Artist($db);

if (isset($_POST["getArtists"])) {
    if (isset($_POST["active"])) {
        $active_status = 1;
    } else {
        $active_status = null;
    }
    $message = $artist->getAllArtists($active_status);
} elseif (isset($_POST["changeStatus"]) && is_numeric($_POST["changeStatus"])) {
    $active = 0;
    if (isset($_POST["active"]) && is_numeric($_POST["active"])) {
        $active = $_POST["active"];
    }
//    $message = $artist->changeActiveStatus($_POST["changeStatus"], $active);
    //Just remove...
    $message = $artist->deleteArtist($_POST["changeStatus"]);
}
echo(json_encode($message));

