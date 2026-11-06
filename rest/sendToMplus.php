<?php

/*
 * Send data to M+ service
 * Vastaanotetaan XML tietue TAI tiedosto.
 * XML tietueessa luodaan objektin sisään uusi "ilmentymä" ja liitetään siihen laitetut tiedot (käyttöliittymässä)
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
$message = "";
require_once REST . 'rest_settings.php';
foreach ($_POST as $key => $value) {
    $message .= "$key ";
    $$key = $value;
}
if (!isset($moduleId)) {
    jsonError("Required arguments missing: ModuleID" . print_r($_POST, true));
    return;
}
$museum = new Museum();
$museum->setModuleId($moduleId);

if (isset($MulOriginalFileDpl)) { //Lähetetään tiedosto M+ järjestelmään
    if (!file_exists($file)) {
        jsonError("File not found: $file");
        return;
    }
    $museum->setFile($file);
    $ok = $museum->sendFile();
    if ($museum->getError() != "") {
        $ok = $museum->getError();
    }
} elseif (isset($sendMPlus)) { //Multimedia XML
    $xml = base64_decode($sendMPlus);
    $museum->setParameter($xml);
    $ok = $museum->fileDefinitions($xml);
} elseif (isset($changeName)) {
    $xml = base64_decode($changeName);
    $ok = $museum->changeFilename($xml);
}
echo($ok);
