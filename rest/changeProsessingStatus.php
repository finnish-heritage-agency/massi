<?php

/**
 * For the CRON...
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
$error = 1;
$retry = 1;
require_once REST . 'rest_settings.php';

if (isset($_POST["row_id"])) {
    $row_id = checkPost($_POST["row_id"]);
}

if (isset($_POST["phase"])) {
    $phase = checkPost($_POST["phase"]);
}

if (isset($_POST["status"])) {
    $status = checkPost($_POST["status"]);
}
if (isset($_POST["error"])) {
    $error = checkPost($_POST["error"]);
}
if (isset($_POST["retry"])) {
    $retry = checkPost($_POST["retry"]);
}
if (!$row_id || !$phase) {
    jsonError("Required arguments missing");
    return;
}

$db = getConnect();
$job = new JobStatus($db, $row_id, $phase, $status);
$message = $job->changeStatus();
if ($error == 0) {
    $job->markRetry($retry);
}

/* Ei lähetetä tiedostoja, jotka ovat saaneet tiedostoIdn
  if ($phase == "lahetys" && $status == 0) {
  $job->removeObjectId($row_id);
  }
 *
 */

echo(json_encode($message));
