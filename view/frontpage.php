<?php

$sended = callRest("POST", WEBROOT . "/rest/stats.php", array("getSended" => 2), true);
$waiting = callRest("POST", WEBROOT . "/rest/stats.php", array("getSended" => 1), true);
$error = callRest("POST", WEBROOT . "/rest/stats.php", array("getSended" => -1), true);



$message = "Sovelluksen ohjeet löytyvät osoitteesta: <pre>L:&#92;Projektit ja työryhmät&#92;Digitaalinen kulttuuriperintötieto&#92;2020&#92;Massadigitointi 2020&#92;Dokumentaatio</pre>";
$message .= "<table class='table table-bordered' width='100%'>\n";
$message .= "   <thead><tr><th>Tapahtuma</th><th>Arvo</th></tr></thead>";
$message .= "    <tbody>\n";
$message .= "       <tr class='text-success'><td>Tiedostoja lähetetty</td><td>$sended</td></tr>\n";
$message .= "       <tr class='text-primary'><td>Tiedostoja jonossa</td><td>$waiting</td></tr>\n";
$message .= "       <tr class='text-danger'><td>Tiedostoja epäonnistunut</td><td>$error</td></tr>\n";
$message .= "    </tbody>\n";
$message .= "</table>\n";

echo makeCard(12, text("ohjeet"), $message, false);
/*
  echo "<div class='row col-md-12'>";
  echo makeStatBox("Tiedostoja lähetetty", $sended, "fa-file");
  echo makeStatBox("Tiedostoja jonossa", $waiting, "fa-file", "success");
  echo makeStatBox("Tiedostoja epäonnistunut", $error, "fa-file", "danger");
  echo "</div>";
  echo "</div>";
 */


/*
  </div>
  <div class='row'>
  <div class='col-xl-3 col-md-6 mb-4'>
  <div class='card border-left-primary shadow h-100 py-2'>
  <div class='card-body'>
  <div class='row no-gutters align-items-center'>
  <div class='col mr-2'>
  <div class='text-xs font-weight-bold text-primary text-uppercase mb-1'>Tiedostoja jonossa</div>
  <div class='h5 mb-0 font-weight-bold text-gray-800'>$sended</div>
  </div>
  <div class='col-auto'>
  <i class='fa fa-file fa-2x text-gray-300'></i>
  </div>
  </div>
  </div>
  </div>
  </div>
  <div class='col-xl-3 col-md-6 mb-4'>
  <div class='card border-left-success shadow h-100 py-2'>
  <div class='card-body'>
  <div class='row no-gutters align-items-center'>
  <div class='col mr-2'>
  <div class='text-xs font-weight-bold text-success text-uppercase mb-1'>Tiedostoja käsitelty</div>
  <div class='h5 mb-0 font-weight-bold text-gray-800'>" . $sended + $waiting . "</div>
  </div>
  <div class='col-auto'>
  <i class='fa fa-file fa-2x text-gray-300'></i>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>";
 */

function makeStatBox($title, $stat, $ico, $color = "primary") {
    $msg = "    <div class='col-xl-3 col-md-6 mb-4'>\n";
    $msg .= "        <div class='card border-left-$color shadow h-100 py-2'>\n";
    $msg .= "            <div class='card-body'>\n";
    $msg .= "                <div class='row no-gutters align-items-center'>\n";
    $msg .= "                    <div class='col mr-2'>\n";
    $msg .= "                        <div class='text-xs font-weight-bold text-$color text-uppercase mb-1'>$title</div>\n";
    $msg .= "                        <div class='h5 mb-0 font-weight-bold text-gray-800'>$stat</div>\n";
    $msg .= "                    </div>\n";
    $msg .= "                    <div class='col-auto'>\n";
    $msg .= "                        <i class='fa $ico fa-2x text-gray-300'></i>\n";
    $msg .= "                    </div>\n";
    $msg .= "                </div>\n";
    $msg .= "            </div>\n";
    $msg .= "        </div>\n";
    $msg .= "    </div>\n";
    return $msg;
}
