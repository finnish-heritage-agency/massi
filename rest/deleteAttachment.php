<?php

/*
 * Delete attachment from M+ service
 * Tarvitaan tiedoston id
 *
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
foreach ($_POST as $key => $value) {
    $message .= "$key ";
    $$key = $value;
}
if (!isset($fileObjectId)) {
    jsonError("Required arguments missing: FileObjectId");
    return;
}
$museum = new Museum();
$message = $museum->deleteAttachment($fileObjectId);
echo($message);

//Käyttö
/*
$data = array("fileObjectId" => $fileObjectId);
$poisto = callRest("POST", WEBROOT . "/rest/deleteAttachment.php", $data, true);
if ($poisto == 1) {
    $viesti .= "Poistettiin epäonnistunut tiedosto M+ järjestelmästä.";
} else {
    $viesti .= "Haamutiedostoa ei saatu poistettua M+ järjestelmästä.";
}
 * 
 */

