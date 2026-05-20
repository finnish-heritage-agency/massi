<?php

header("Access-Control-Allow-Methods: POST");
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$db = getConnect();
$collection = new Collection($db);
if (isset($_POST["reset"])) {
    $message = $collection->resetAttempts();
} else {
    $message = $collection->checkAllOk();
}
echo(json_encode($message));
