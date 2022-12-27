<?php

/**
 * Lisätään uusi tekijä
 *
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;

if (isset($_POST["internal_id"])) {
    $id = checkPost($_POST["internal_id"]);
}

if (isset($_POST["name"])) {
    $name = checkPost($_POST["name"]);
}
if (!$id || !$name) {
    jsonError("Required arguments missing");
    return;
}
$db = getConnect();
$artist = new Artist($db, 0, $name, $id, 1);

if (isset($_POST["addAnArtist"])) {
    $message = $artist->addAnArtist();
}
echo(json_encode($message));
