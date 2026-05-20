<?php

/*
 * Used by collection site
 */
header("Expires: Mon, 20 Dec 1998 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . '../settings.php';
$lista_id = $_GET["id"];
$check = 0; //Tarkistetaan vain 10 ekaa, koska voi olla ajo vielä kesken...
$data = array("oneJob" => 1, "lista_id" => $lista_id);
$tmp = callRest("POST", WEBROOT . "/rest/getJobs.php", $data, true);
$message = "<table class='table table-bordered text-center' id='dataTable' width='100%'>\n";
$message .= "   <thead><tr><th>Päiväys</th><th>Kokoelmatunnus</th>";
foreach (JOB_PHASES as $phase) {
    if ($phase == "lahetys") {
        $phase = "Lähetys";
    } elseif ($phase == "nayttokuvat") {
        $phase = "Näyttökuvat";
    }
    $message .= "           <th>" . ucfirst($phase) . "</th>\n";
}
$message .= "<th>Valmis</th>";
$message .= "</tr></thead>\n";
$message .= "    <tbody>\n";
foreach ($tmp as $row) {
    $ready = true;
    $lisays = "";
    /*
      $data = array("checkCollectionId" => 1, "collection_id" => $row->kokoelmatunnus);
      $tmp2 = callRest("POST", WEBROOT . "/rest/getJobs.php", $data, true);
      debug($tmp2);
     *
     */
    $tmp = $log_site = "<a href='javascript:void(0);' class='btn btn-sm btn-outline-danger' onClick=\"window.open('" . WEBROOT . "/sivu/eranLokit/$lista_id/" . $row->rivi_id . "', 'LOKIT', 'location=yes,menubar=0,resizable=1'); return false;\">\n";
    $message .= "       <tr>\n";
    $sorting = sortDate(date("d.m.Y H:i:s", strtotime($row->aloitettu)));
    $message .= "<td class='pointer'>" . $sorting["sort"];
    $message .= " (" . $sorting["week_day"] . ") " . $sorting["day"] . "</td>\n";
    $message .= "           <td>" . $row->kokoelmatunnus . "</td>\n";
    foreach (JOB_PHASES as $phase) {
        //fa-sync-alt fa-spin
        if ($phase == "tarkistus" && $row->$phase == 0) {
            $ready = false;
        }
        if ($ready == false && $check < 200) {
            $check++;
            $data = array("checkCollectionId" => 1, "collection_id" => $row->kokoelmatunnus);
            $tmp2 = callRest("POST", WEBROOT . "/rest/getJobs.php", $data, true);
            if ($tmp2 != "") {
                $row->$phase = 98; //Ns. toisessa erässä viety
                $row->rivi_valmis = 98;
                $lisays = "<br />Kokoelmatunnus löytyy listalta <a href='" . WEBROOT . "/sivu/era/$tmp2/'>$tmp2</a>.";
            }
        }

        $message .= "           <td>" . showIcon($row->$phase, false, $log_site) . "</td>\n";
    }

    $message .= "           <td>" . showIcon($row->rivi_valmis) . " ";
    if ($row->valmistunut != "") {
        $sorting = sortDate(date("d.m.Y H:i:s", strtotime($row->valmistunut)));
        $message .= $sorting["sort"];
        $message .= $sorting["day"];
    }
    $message .= "$lisays</td>\n";
//    $message .= "<td><a href='javascript:void(0);' class='btn btn-sm' onClick=\"window.open('" . WEBROOT . "/view/checkCollectionLogs.php?id=$lista_id', 'LOKIT', 'location=no,menubar=0,resizable=1,left=100,top=100,width=600,height=600'); return false;\">";
    $message .= "       </tr>\n";
}
$message .= "    </tbody>\n";
$message .= "</table>\n";
echo $message;
echo "Sivusto on päivitetty klo: " . date("H:i:s", time());

