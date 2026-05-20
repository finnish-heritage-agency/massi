<?php

/**
 * When all is done we change all old thumbails to false
 */
if (file_exists(__DIR__ . "/../settings.php")) {
    require_once __DIR__ . "/../settings.php";
} else {
    die(":/ \n");
}
$x = shell_exec('whoami');
$x = preg_replace('/\s+/', '', $x);
$msg = "";
if ($x != ROOT_USER) {
    die();
}

$pids = (int) shell_exec("ps ax | grep 'php " . CRON_FOLDER . "changeThumbnailBoo.php' | grep -v 'grep' | wc -l");
if ($pids > 1) {
    echo "changeThumbnailBoo.php on jo käynnissä. $pids";
    die();
}

$message = "";
$tmp_data = array("phase" => "lahetys", "status" => 2);
$tmp = callRest("POST", WEBROOT . "/rest/getJobs.php", $tmp_data, true); //haetaan semmoiset rivit, joissa tarkistus = 2 ja valmis = 0

if (count($tmp) > 0) {
    foreach ($tmp as $row) {
        if ($row->nayttokuvat == 2) {
            continue;
        } elseif ($row->tarkistus != 2 || $row->metatiedot != 2 || $row->lahetys != 2) {
            continue;
        }
        //$log_file = str_replace(":", "_", str_replace(".", "_", $row->kokoelmatunnus));
        //26.9.2022 Halutaan hakemistoon jättää pistenimi
        $log_file = str_replace(":", "_", $row->kokoelmatunnus);
        $connected = checkMPlusStatus();
        if ($connected != "OK") {
            $message .= "$connected\n";
            $message = writeLog($message, $log_file, true);
            die();
        }
        timerStart();
        echo date("H:i:s", time()) . " changeThumbnailBoo.php: " . $row->kokoelmatunnus . " \n";
        $data = new SendCollection($row->kokoelmatunnus, false, MUSEUM_DOMAIN, $row->listan_rivi_id);

        $data->changeStatus(9, "nayttokuvat");
        $text = $data->disableOldThumbnails($row->objektin_id);

        if ($text == -1) {
            $send_error = true;
            $data->changeStatus(-1, "nayttokuvat");
            $message .= "##### EPÄONNISTUI ##### \n";
        } else {
            $send_error = false;
            $message .= $text;
            $data->changeStatus(2, "nayttokuvat");
            $data->changeStatus(2, "rivi_valmis");
            $tmp_data = array("changeCollectionRowStatus" => $data->getRowId(), "progress" => 2);
            $tmp = callRest("POST", WEBROOT . "/rest/editCollection.php", $tmp_data);
            $valmis = date("Y-m-d H:i:s", time());
            $data->changeStatus($valmis, "valmistunut");
            $message .= "##### VALMIS ##### \n";
        }
        $message = writeLog($message, $log_file, $send_error);
    }
}