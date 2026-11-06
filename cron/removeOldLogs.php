<?php

/**
 * Removing old logs and files wich has been completly moved
 */
if (file_exists(__DIR__ . "/../settings.php")) {
    require_once __DIR__ . "/../settings.php";
} else {
    die(":/ \n");
}
$x = shell_exec('whoami');
$x = preg_replace('/\s+/', '', $x);
$msg = "";
if ($x != ROOT_USER) {
    die();
}
timerStart();
$msg = "removeOldLogs.php --> (" . SAVE_LOGS . " days olders )";
if (!is_numeric(SAVE_LOGS)) {
    $save = 999;
} else {
    $save = SAVE_LOGS;
}
if (LOGS != "" && LOGS != "/" && ROOT != "" && ROOT != "/") {
    shell_exec("find " . LOGS . " -maxdepth 1 -mtime +$save -type f  -name '*-log' -delete");
    $x = shell_exec("find " . ROOT . "rest/logs/ -maxdepth 1 -mtime +$save -type f  -name '*log' -delete");
    $msg .= "poistettu ";
}
$msg .= lapTime() . " \n";

echo $msg;
