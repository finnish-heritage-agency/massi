<?php

/**
 * Eri tyyppisiä lukuja järjestelmästä.
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;
$db = getConnect();

if (isset($_POST["getSended"]) && $_POST["getSended"] == 2) {
    $message = countFiles(2);
} else {
    $message = countFiles(0);
    if ($message == 0) {
        $message = "-";
    }
}

echo(json_encode($message));

function countFiles($status = 2) {
    global $db;
    try {
        $sql = "SELECT count(lahetetty) FROM tiedostot WHERE lahetetty = $status";
        if (!$stmt = $db->prepare($sql)) {
            writeToLog("Cannot prepare stament: $sql");
            return -3;
        }

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
