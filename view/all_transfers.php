<?php

/**
 * https://museo.softrepo.fi/cron/readFolder.php
 */
$url = WEBROOT . "/sivu/siirrot/";
$message = "<a href='" . WEBROOT . "/pdfgenerator/index.php?url=$url' class='text-right no_pdf' target='_blank'><i class='fa fa-print' aria-hidden='true'></i> " . text("print") . "</a>";
if (isset($_GET["id"]) && $_GET["id"] == "refresh") {
    $message .= "<div id='all_jobs' style='width: 100%;></div>";
} else {
    $data = array("jobs" => 1, "all" => true);
    $tmp = callRest("POST", WEBROOT . "/rest/getJobs.php", $data, true);

    $message .= "<table class='table table-bordered text-center' id='dataTable' width='100%'>\n";
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
        $message .= "           <td>" . showIcon($row->tarkistus, true) . "</td>\n";
        $message .= "           <td>" . showIcon($row->metatiedot, true) . "</td>\n";
        $message .= "           <td>" . showIcon($row->lahetys, true) . "</td>\n";
        $message .= "           <td>" . showIcon($row->rivi_valmis, true) . " ";
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
}

echo makeCard(12, text("all transfers"), $message, false);
