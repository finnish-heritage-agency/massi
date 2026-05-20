<?php

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

$pids = (int) shell_exec("ps ax | grep 'php " . CRON_FOLDER . "prosessingFolders.php' | grep -v 'grep' | wc -l");
if ($pids > 1) {
    echo "prosessingFolders.php on jo käynnissä. $pids";
    die();
}
if (!file_exists(EXIFTOOL)) {
    writeLog("EXIFTOOL on asentamatta");
}
$message = "";
$error_text = "";
$xml_error = false;
$send_error = false;
$tmp_data = array("phase" => "tarkistus", "status" => 2);
$tmp = callRest("POST", WEBROOT . "/rest/getJobs.php", $tmp_data, true); //haetaan semmoiset rivit, joissa tarkistus = 2 ja valmis = 0

if (count($tmp) > 0) {
    foreach ($tmp as $row) {
        if ($row->lahetys == 2 || $row->kokoelmatunnus == "") { //Jumissa tai jo valmiina...
            continue;
        }
        //$log_file = str_replace(":", "_", str_replace(".", "_", $row->kokoelmatunnus));
        //26.9.2022 Halutaan hakemistoon jättää pistenimi
        $log_file = str_replace(":", "_", $row->kokoelmatunnus);
        $connected = checkMPlusStatus();
        if ($connected != "OK") {
            echo "M+ yhteys: $connected\n";
            $message .= "$connected\n";
            $message = writeLog($message, $log_file, true);
            die();
        }
        $thumb_ok = false; //Tekee objektin ensimmäisestä tiedostosta näyttökuvan
        timerStart();
        echo date("H:i:s", time()) . " prosessingFolders.php: " . $row->kokoelmatunnus . " \n";
        $cataloged = false;
        if ($row->objektin_id != "") {
            $cataloged = true;
        }
        $data = new SendCollection($row->kokoelmatunnus, $cataloged, MUSEUM_DOMAIN, $row->listan_rivi_id);
        $data->setModule("multimedia"); //Lähetetään tiedot multimedia objektiin
        $data->makeDefinitions();
        $data->changeStatus(9, "lahetys");
        if ($row->metatiedot != 2) {//Jos pelkkä lähetys tehdään uusiksi
            $data->changeStatus(9, "metatiedot");
        }

        if ($data->getError() != "") {
            writeLog("Aineisto: " . $row->kokoelmatunnus . ". " . $data->getErrorText(), $log_file, true);
            $data->changeStatus(0, "lahetys");
            $data->changeStatus(-1, "metatiedot");
            continue;
        }
        /*
         * XML tietojen lähetys ja tiedostoille tiedosto IDT
         */
        foreach ($data->getFiles() as $file) {
            $tmp_data = array("getSavedFileData" => 1, "filename" => $file->getBasename());
            $saved_data = callRest("POST", WEBROOT . "/rest/collections.php", $tmp_data, true); // check is file_object_id generated already
            if (!isset($saved_data->file_object_id) || !is_numeric($saved_data->file_object_id) || $saved_data->file_object_id == 0) {
                $file_id = $data->sendMultimediaContent($file);
            } else {
                $file_id = $saved_data->file_object_id;
                $message .= "Tiedoston " . $file->getBasename() . " metatiedot on jo lähetetty MuseumPlus järjestelmään.\n";
            }

            if ($file_id <= 0) {
                $error_text .= "Tiedoston " . $file->getBasename() . " metatietoja ei saada lähetettyä MuseumPlus järjestelmään.\n";
                $viesti = writeLog("$ok\n", $log_file . "_mplus", true);
                $xml_error = true;
            }
        }

        if ($xml_error == true) {
            $data->changeStatus(-1);
            $message .= "##### METATIETOJEN LÄHETYS EPÄONNISTUNUT #####\n$error_text\n";
        } else {
            $data->changeStatus(2); //XML on valmis
        }
        $message = writeLog($message, $log_file, $xml_error);
        if ($xml_error == true) {
            die();
        }
        foreach ($data->getFiles() as $file) { //Lähetetään tiedostot
            $tmp_data = array("getSavedFileData" => 1, "filename" => $file->getBasename());
            $saved_data = callRest("POST", WEBROOT . "/rest/collections.php", $tmp_data, true); // check is file_object_id generated already
            if (isset($saved_data->file_object_id) && is_numeric($saved_data->file_object_id) && $saved_data->file_object_id > 0) {
                $file_id = $saved_data->file_object_id;
            } else {
                $message .= "Tiedostostolle " . $saved_data->tiedosto . " ei saada IDtä\n";
                continue;
            }

            if ($file_id > 1 && $saved_data->lahetetty == 0) {
                $return = $data->sendMultimediaFile($file, $file_id);
                if ($return != 1) {
                    $send_error = true;
                    $error_text .= "Tiedoston " . $file->getBasename() . " lähettäminen MuseumPlus järjestelmään epäonnistui.\n";
                    $data->saveCountOfTheTries();
                    $viesti = writeLog("$return\n", $log_file . "_mplus", true);
                } else {//Muutetaan tiedostonnimi M+ järjestelmään
                    $xml = $data->getChangeNameXml($file->getBasename(), $file_id);
                    $tmp_data = array("changeName" => base64_encode($xml), "moduleId" => $file_id);
                    $ok = callRest("POST", WEBROOT . "/rest/sendToMplus.php", $tmp_data, true);
                    $viesti = writeLog("\nXML Change name: $file_id\n $xml \n XML STOPS\n", basename($file->getFolder()) . "_xml", false);
                    if ($ok != 1) {
                        $error_text .= "Tiedoston (" . $file->getBasename() . ") nimen vaihtaminen MuseumPlus järjestelmässä epäonnistui.\n";
                        $data->saveCountOfTheTries();
                        $viesti = writeLog("$ok\n", $log_file . "_mplus", true);
                    }
                }
            } else {
                $message .= "Tiedosto " . $saved_data->tiedosto . " on jo lähetetty. ID: $file_id \n";
            }
            $message = writeLog($message, $log_file, $send_error);
        }
        if ($send_error == true) {
            $data->changeStatus(-1, "lahetys");
            $message .= "##### SIIRTO EPÄONNISTUNUT ##### \n$error_text\n";
            $message = writeLog($message, $log_file, $send_error);
            die(); //Lisätty 22.4. Jos tulee ongelmia lähetyksessä, niin stopataan koko setti
        } else {
            $data->changeStatus(2, "lahetys");
            $message .= "##### VALMIS ##### \n";
            $message = writeLog($message, $log_file, $send_error);
        }
    }
} else {
    $message = "Ei löytynyt uusia rivejä...";
    $message = writeLog($message);
}