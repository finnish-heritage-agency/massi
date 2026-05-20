<?php

/**
 * Haetaan tiedoston File_id. ID on saatu M+:sta ja tallennettu tietokantaan
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;
$db = getConnect();
foreach ($_POST as $key => $value) {
    $$key = checkPost($value);
}

if (!$id || !$objectId) {
    jsonError("Required arguments missing");
    return;
} else {
    $message = getFileObjectId($id, $objectId);
}

echo(json_encode($message));

function getFileObjectId($id, $objectId) {
    $return = 0;
    global $db;
    try {
        $sql = "SELECT file_object_id FROM tiedostot, listan_rivit WHERE listan_rivit.objektin_id = :objekti_id
                AND tiedostot.listan_rivi_id = listan_rivit.rivi_id AND tiedostot.file_object_id = :id";
        if (!$stmt = $db->prepare($sql)) {
            writeToLog("Cannot prepare stament: $sql");
            return -3;
        }

        $stmt->bindParam(":objekti_id", $objectId);
        $stmt->bindParam(":id", $id);
        if (!$stmt->execute()) {
            writeToLog("Cannot execute prepared statement for: $sql");
            return -2;
        }
        $return = $stmt->fetchColumn();
    } catch (Exception $error) {
        writeToLog($error->getMessage());
        return -1;
    }
    return $return;
}
