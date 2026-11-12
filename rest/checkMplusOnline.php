<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';

$museum = new Auth();
$museum->login();
// $message = $config_file;
$message = "";

file_put_contents(
    "/tmp/checkMplusOnline.debug",
    date("Y-m-d H:i:s")
    . " STATUS="
    . var_export($museum->getLoginStatus(), true)
    . " TEXT="
    . var_export($museum->getLoginText(), true)
    . PHP_EOL,
    FILE_APPEND
);

if ($museum->getLoginStatus() > 0 && $museum->getLoginStatus() != "") {
    $message = "OK";
} else {
    $message = "connection error: " . $museum->getLoginText();
}
echo(json_encode($message));
