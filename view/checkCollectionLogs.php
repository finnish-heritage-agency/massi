<?php

/*
 * Javascript popup windows. uses default functions, so it cannot be in rest folder
 */
$msg = "<div id='content'>\n"; //<!-- Main Content -->
$id = checkNumber($_GET["id"]);
$rivi_id = checkNumber($_GET["rivi_id"]);
if ($id >= 0) {
    $data = array("getOneJobForLogs" => 1, "lista_id" => $id, "rivi_id" => $rivi_id);
    $tmp = callRest("POST", WEBROOT . "/rest/getJobs.php", $data, true);
}
if (isset($_GET["uudelleen_kasittelyyn"])) {
    $row_id = checkNumber($_GET["row_id"]);
    $phase = checkPost($_GET["phase"]);
    $status = checkNumber($_GET["status"]);
    $retry = 1;
    $data1 = array("row_id" => $row_id, "phase" => $phase, "status" => $status, "error" => 0, "retry" => $retry);
    $ok = callRest("POST", WEBROOT . "/rest/changeProsessingStatus.php", $data1, true);
    if ($ok == 1) { //Cannot make sweet popup front of the javascript window...
//        $_SESSION["tallennus_ok"] = "Erä laitettu uudelleenkäsittelyyn";
    } else {
//        $_SESSION["tallennusvirhe"] = "Ei saatu laitettua uudelleenkäsittelyyn. SYY: $ok"; //Handling error
    }
    header("location: ./");
}

$msg .= "<p>ID: $rivi_id Tarkistetaan käsiteltävät hakemistot <strong>" . PICTURE_FOLDER . "</strong> hakemistosta. Hakemisto pitää olla myös valmiina digitointierässä.<br />";
$msg .= "Aineisto kopioidaan M+ järjestelmään <strong>" . SEND_TIME . "</strong> välisenä aikana. Muina aikoina viedään vain aineistojen metatietoja.</p>";

foreach ($tmp as $row) {
    $status_bar = "<div class='container-fluid phase_bar'><br />";
    foreach (JOB_PHASES as $phase) {
//        $phase_status[$phase] = $row->$phase;
        if ($row->$phase == 9 || $row->$phase == 99) {
            //$status_bar .= "<div class='spinner-border spinner-border-sm' role='status' title='$phase'><span class='sr-only'>Loading...</span></div>\n";
            $status_bar .= "<span class='badge badge-default' title='$phase työnalla'><i class='fas fa-hourglass-half'> $phase</i></span>\n";
        } elseif ($row->$phase == 2) {
            $status_bar .= "<span class='badge badge-success' title='$phase on valmis'><i class='fas fa-check' ></i> $phase</span>\n";
        } elseif ($row->$phase == 1) {
            //$status_bar .= "<div class='spinner-border spinner-border-sm' role='status'<span class='sr-only'>Loading...</span></div>\n";
            $status_bar .= "<span class='badge badge-default' title='$phase aloitetaan kohta'><i class='fas fa-hourglass'> $phase</i></span>\n";
        } elseif ($row->$phase < 0) {
            $status_bar .= "<span class='badge badge-danger' title='$phase:ssa on ogelmia'><i class='fas fa-exclamation'> $phase</i></span>\n";
            $status_bar .= "<a href='" . WEBROOT . "/sivu/eranLokit/$id/&uudelleen_kasittelyyn=1&row_id=" . $row->listan_rivi_id . "&phase=$phase&status=0' class='btn btn-outline-danger btn-sm text-right'>Uudelleenkäsittelyyn</a>";
        } else {
            $status_bar .= "<span class='badge badge-default' title='$phase odottaa aloitusta'><i class='fas fa-hourglass-start'> $phase </i></span>\n";
        }
    }


    $status_bar .= "<span class='badge badge-default text-right' title='Tiedostojen lkm'><i class='far fa-images'> " . $row->tiedostojen_lkm . "</i></span>\n";
    $status_bar .= "</div>\n";
    $job = new Job($row->tyo_id, $row->listan_rivi_id, $row->kokoelmatunnus);
//    $job->setJobPhaseStatuses($phase_status);
    $job->setStartTime($row->aloitettu);
    if (file_exists($job->getLogFile())) {
        $text = nl2br(strip_tags(file_get_contents($job->getLogFile())));
    } else {
        $text = "Prosessista ei ole lokitietoja tallessa.";
    }
    $message .= makeCard(12, $row->kokoelmatunnus . "<span class='text-right'>" . $job->getStartTime() . "</span>", "<pre>$text</pre>", false, $status_bar);
}
echo makeCard(12, text("handling"), $msg, false);

echo "<div class='row'>\n";
echo $message;

echo "</div>";
echo "</div>";
