<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;
$row_id = 0;

if (isset($_POST["row_id"])) {
    $row_id = checkPost($_POST["row_id"]);
}

$db = getConnect();
$job = new JobStatus($db, $row_id);

if (isset($_POST["newJob"])) {
    $message = $job->addANewJob();
} elseif (isset($_POST["newJobsbyListaId"])) {
    if (isset($_POST["lista_id"])) {
        $lista_id = checkPost($_POST["lista_id"]);
    }
    $message = $job->addAllRowsByListaId($lista_id);
}
echo(json_encode($message));

