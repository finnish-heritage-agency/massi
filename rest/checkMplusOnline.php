<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';

$museum = new Auth();
$museum->login();
$message = $config_file;

if ($museum->getLoginStatus() > 0 && $museum->getLoginStatus() != "") {
    $message = "OK";
} else {
    $message = "connection error: " . $museum->getLoginText();
}
echo(json_encode($message));
