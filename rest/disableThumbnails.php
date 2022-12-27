<?php

/**
 * Disable all old thumbnails
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
$message = "";
require_once REST . 'rest_settings.php';
$db = getConnect();
foreach ($_POST as $key => $value) {
    $$key = $value;
}
if (!isset($moduleId)) {
    jsonError("Required arguments missing: ModuleID" . print_r($_POST, true));
    return;
}
$museum = new Museum();
$museum->setModuleId($moduleId);

$ids = $museum->checkTrueThumbnailBoos();
$row = 0;
foreach ($ids as $id) {
    if (sendedByMassaSoftware($id, $moduleId) == 0) {
        $row++; //Tarkistetaan tehdäänkö sama merkintämonesti... 15.4.2021. Voidaan poistaa noin 20.4.2021
        $museum->setDisableId($id);
        $changed = $museum->changeThumbnailBooStatus();
        if ($changed == -2 || $changed == -1) {
            $message = "";
            echo(json_encode($message));
            die();
        }

        $message .= "Rivi: $row. ID: $id. Status: $changed. ";
    }
}
echo(json_encode($message));

