<?php

header("Expires: Mon, 20 Dec 1998 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . '../settings.php';

$data = array("jobs" => 1, "all" => true);
$tmp = callRest("POST", WEBROOT . "/rest/getJobs.php", $data, true);

$message = "<table class='table table-bordered text-center' id='dataTable' width='100%'>\n";
$message .= "   <thead><tr><th>Päiväys</th><th>Digitointierä</th><th>Kokoelmatunnus</th><th>Tarkistus</th><th>Metatiedot</th><th>Lähetys</th><th>Valmis</th>";
$message .= "</tr></thead>\n";
$message .= "    <tbody>\n";
foreach ($tmp as $row) {
    $message .= "       <tr>\n";
    $sorting = sortDate(date("d.m.Y H:i:s", strtotime($row->aloitettu)));
    $message .= "<td class='pointer'>" . $sorting["sort"];
    $message .= " (" . $sorting["week_day"] . ") " . $sorting["day"] . "</td>\n";
    $message .= "           <td>" . $row->otsikko . "</td>\n";
    $message .= "           <td>" . $row->kokoelmatunnus . "</td>\n";
    $message .= "           <td>" . showIcon($row->tarkistus) . "</td>\n";
    $message .= "           <td>" . showIcon($row->metatiedot) . "</td>\n";
    $message .= "           <td>" . showIcon($row->lahetys) . "</td>\n";
    $message .= "           <td>" . showIcon($row->rivi_valmis) . " ";
    if ($row->valmistunut != "") {
        $sorting = sortDate(date("d.m.Y H:i:s", strtotime($row->valmistunut)));
        $message .= $sorting["sort"];
        $message .= $sorting["day"];
    }
    $message .= "</td>\n";
    $message .= "       </tr>\n";
}
$message .= "    </tbody>\n";
$message .= "</table>\n";
echo $message;
