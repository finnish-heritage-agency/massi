<?php

/**
 * Tallennetaan uusi keräys tietokantaan
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
header("Access-Control-Allow-Methods: POST");

$license_id = 0;
$artist_id = 0;
$type_id = 0;
$row = 0;
$error_row = "";
$finna = 0;

if (isset($_POST["rows"])) {
    $tmp_parser = $_POST["rows"]; //can´t use checkpost :/
    foreach (PARSER as $parser) {
        $tmp_parser = str_replace("$parser", "  ", $tmp_parser);
        //$tmp_parser = str_replace(";", "", $tmp_parser);
    }
    $tmp = explode("  ", $tmp_parser);
}

if (isset($_POST["title"])) {
    $title = checkPost($_POST["title"]);
}
if (!$tmp || !$title) {
    jsonError("Required arguments missing");
    return;
}


if (isset($_POST["legal_id"])) {//Optionals...
    $license_id = checkPost($_POST["legal_id"]);
}
if (isset($_POST["type_id"])) {//Optionals...
    $type_id = checkPost($_POST["type_id"]);
}

if (isset($_POST["artist_id"])) {//Optionals...
    $artist_id = checkPost($_POST["artist_id"]);
}
if (isset($_POST["finna"])) {//Optionals...
    $finna = checkPost($_POST["finna"]);
}

if (isset($_POST["batch_saver"])) {
    $batch_saver = checkPost($_POST["batch_saver"]);
}
$db = getConnect();

$collection = new Collection($db);
$collection->setCollectionTitle($title);
$collection->setFinna($finna);
$collection->setArtistId($artist_id);
$collection->setLicenseId($license_id);
$collection->setLegalTypeId($type_id);
$collection->setBatchSaver($batch_saver);

$id = $collection->addCollection();
if ($id > 0) {
    $error_rows = 0;
    $error_row = "Virheet riveillä:<br />";
    foreach ($tmp as $tmp_row) {
        if ($tmp_row == "") {
            continue;
        }
        $row++;
        $ok = $collection->addRowsToCollection($tmp_row);
        if ($ok != 1) {
            $error_rows++;
            $error_row .= "Rivi: $row ($tmp_row) <br />";
        }
    }
    $message = "Tallennettu.";
    if ($error_rows > 0) {
        $message .= "Virheellisiä rivejä: $error_rows kpl. $error_row";
    } else {
        $message .= "Tallentaessa ei tullut virheitä.";
    }
} else {
    $message = "Kokoelmaa ei saatu tallennettua!";
}

echo(json_encode($message));
