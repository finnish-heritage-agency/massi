<?php

$msg = null;
$files = array_slice(array_filter(scandir(LOGS)), 2); //skips . & ..
foreach ($files as $log_file) {
    if (strpos($log_file, "success") !== false) { //Piilotetaan success tiedostot
        continue;
    }
    $text = file_get_contents(LOGS . "$log_file");
    $msg .= makeCard(4, $log_file, nl2br(strip_tags($text)));
}

//Not best practice, because rest service is independent service....
$rest = array_slice(array_filter(scandir("./rest/logs/")), 2);
foreach ($rest as $log_file) {
    if (strpos($log_file, "success") !== false) { //Piilotetaan success tiedostot
        continue;
    }
    $text = file_get_contents("./rest/logs/$log_file");
    $msg .= makeCard(4, $log_file, nl2br(strip_tags($text)));
}
echo "<div class='row'>";
echo $msg;
echo "</div>\n";
