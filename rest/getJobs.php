<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$db = getConnect();
$jobs = new JobStatus($db);

if (isset($_POST["phase"])) {
    $phase = checkPost($_POST["phase"]);
    if (isset($_POST["status"])) {
        $status = checkPost($_POST["status"]);
    }
    if (!$status || !$phase) {
        jsonError("Required arguments missing");
        return;
    }
    $jobs->setPhase($phase);
    $jobs->setStatus($status);
    $message = $jobs->getCurrentJobs();
} elseif (isset($_POST["getOneJobForLogs"])) {
    if (isset($_POST["lista_id"])) {
        $lista_id = checkNumber($_POST["lista_id"]);
    }
    if (isset($_POST["rivi_id"])) {
        $rivi_id = checkNumber($_POST["rivi_id"]);
        $lista_id = -1; //we are using rivi_id, but we need some value for lista_id
    }
    if (!$lista_id) {
        jsonError("Required arguments missing");
        return;
    }
    if ($rivi_id != "") {
        $message = $jobs->getOneJobForLogs($rivi_id, "rivi_id");
    } else {
        $message = $jobs->getOneJobForLogs($lista_id);
    }
} elseif (isset($_POST["oneJob"])) {
    if (isset($_POST["lista_id"])) {
        $lista_id = checkNumber($_POST["lista_id"]);
    }
    if (!$lista_id) {
        jsonError("Required arguments missing");
        return;
    }
    $message = $jobs->getOneJob($lista_id);
} elseif (isset($_POST["checkCollectionId"])) {
    $message = $jobs->checkCollectionId($_POST["collection_id"]);
} else {
    if (isset($_POST["all"]) && $_POST["all"] == true) {
        $all = true;
    } else {
        $all = false;
    }
    $message = $jobs->getAllJobs($all);
}


echo(json_encode($message));

