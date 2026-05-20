<?php

/*
 * JREPO 10.9.2020
 * Muokattu 22.1.2022. Lisätty sääntö 4
 * Tämä ottaa valmiin skannatun kokoelman (yksi tunnus, jossa 1-x määrä kuvia) käsittelyyn
 * Lokitusta voi lukea frontendissä. tulostaa cronille vain infoviestejä
 * 1. Luetaan käsiteltävä hakemisto
 * 2. Tarkistetaan onko kyseinen objektinnimi valmis tietokannasta (listan_rivit valmis sarake)
 * 3. Merkitään rivi käsittelyyn (9)
 * 4. Jos palauttaa >1, niin onnistunut
 *  1 = on jo lisätty
 *  Jos syystä X ei löydy tyot taulusta, niin lisätään se sinne
 *  <0 = virheitä
 * Jos objekti id on tyhjä yritetään sekin saada lisättyä...
 * VALMIS
 */

if (file_exists(__DIR__ . "/../settings.php")) {
    require_once __DIR__ . "/../settings.php";
} else {
    die(":/ \n");
}
$x = shell_exec('whoami');
$x = preg_replace('/\s+/', '', $x);
$msg = "";
$stop_process = false; // IF true, process is still assembling  files...
if ($x != ROOT_USER) {
    die();
}
$x = shell_exec("find " . PICTURE_FOLDER . " -maxdepth 1 -type f");
if ($x != "") {
    echo "Tiedostoja lajittelematta. ";
    die();
}
$pids = (int) shell_exec("ps ax | grep 'php " . CRON_FOLDER . "readCompleteFolders.php' | grep -v 'grep' | wc -l");
if ($pids > 1) {
    //echo shell_exec("ps ax | grep 'php " . CRON_FOLDER . "readCompleteFolders.php' | grep -v 'grep'");
    echo "readCompleteFolders.php on jo käynnissä. $pids ";
    die();
}


$folders = array_slice(array_filter(scandir(PICTURE_FOLDER)), 2);
if (count($folders) > 0) {
    foreach ($folders as $folder) {
        $error = false;
        $message = null;
        $filecount = 0;
        if (is_dir(PICTURE_FOLDER . $folder)) {
            if (substr_count($folder, "_") > 1) { //objektinnimessä alaviiva
                $object_name = str_lreplace("_", ":", $folder);
            } else {
                $object_name = str_replace("_", ":", $folder);
            }
            $data = array("isCollectionReady" => $object_name);
            $tmp = callRest("POST", WEBROOT . "/rest/collections.php", $data, true); //Is collection folder ready...

            if (!isset($tmp->valmis)) { //objektinimessä onkin piste.. huoh
                $object_name = str_lreplace("_", ".", $folder);
                $object_name = str_replace("_", ":", $object_name);
                $data = array("isCollectionReady" => $object_name);
                $tmp = callRest("POST", WEBROOT . "/rest/collections.php", $data, true); //Is collection folder ready...
            }
            if (isset($tmp->valmis) && $tmp->valmis == 1 && $tmp->tarkistus == 0) {
                if ($tmp->objektin_id == "") {//Yhteys on välillä todella pitkä ja tästä syystä ei saada aina noita ID:tä... Sen takia se on osa tätä prosessia...
                    $connected = checkMPlusStatus();
                    if ($connected != "OK") {
                        echo "M+ yhteys: $connected\n";
                        die();
                    }
                    timerStart();
                    $message .= "Käsitellään aineisto $object_name --> ";
                    $object_id = callRest("POST", WEBROOT . "/rest/getObjectId.php", array("search" => $object_name, "re_tries" => RE_TRIES), true);
                    if ($object_id != "" && is_numeric($object_id)) { //Let´s save also object id
                        $data2 = array(
                            "saveCollectionId" => 1,
                            "object_id" => $object_id,
                            "object_name" => $object_name,
                        );
                        $tmp2 = callRest("POST", WEBROOT . "/rest/editCollection.php", $data2);
                        $message .= "Objectinnro: $object_id. Tieto saatu tallennettua tietokantaan: " . changeReturn($tmp2) . " --> ";
                    } else {
                        $data1 = array("row_id" => $tmp->rivi_id, "phase" => "tarkistus", "status" => -1);
                        $tmp3 = callRest("POST", WEBROOT . "/rest/changeProsessingStatus.php", $data1, true);

                        $message .= "Objektille ei saatu haettua ID:tä M+ järjestelmästä.\n";
                        $message = writeLog($message, $folder, true);
                        /*
                          $message .= "Objektille ei saada ID:tä. Kokeillaan uudestaan seuraavassa ajossa.\n";
                          $data1 = array("saveCountOfTheTries" => 1, "kokoelmatunnus" => $object_name);
                          $response = callRest("POST", WEBROOT . "/rest/collections.php", $data1, true);
                          $message = writeLog($message, $folder, true);
                         *
                         */
                    }
                } else {
                    $object_id = $tmp->objektin_id;
                }
                if (is_numeric($object_id) && is_numeric($tmp->tarkistus) && $tmp->tarkistus == 0) { //Pitää löytyi tietokannasta
                    $data1 = array("row_id" => $tmp->rivi_id, "phase" => "tarkistus", "status" => 9);
                    $tmp3 = callRest("POST", WEBROOT . "/rest/changeProsessingStatus.php", $data1, true);

                    if ($tmp3 < 0) {
                        $message .= "Aineistoa ei saatu lukittua!\n";
                    } elseif ($tmp3 == 1) {
                        $message .= "Aineisto on laitettu käsittelyjonoon -->\n";
                    } else {
                        $message .= "Aineisto on käsittelyssä -->\n ";
                    }
                    $tmp_files = array_slice(array_filter(scandir(PICTURE_FOLDER . $folder)), 2);
                    if ($tmp_files) {
                        $added = 0;
                        $filecount = count($tmp_files);
                        foreach ($tmp_files as $file) {
                            $tmp_data = array("newFile" => 1, "file" => $file, "folder" => PICTURE_FOLDER . $folder, "row_id" => $tmp->rivi_id);
                            $ok = callRest("POST", WEBROOT . "/rest/addNewFile.php", $tmp_data, true);
                            if (is_numeric($ok) && $ok > 0) {
                                $added++;
                            }
                        }
                    }
                    //TODO: mihin laitetaan viesti, jos hakemistosta ei löydy tiedostoja?
                    if ($filecount == 0) { //hakemisto on vielä tyhjä
                        $data1 = array("row_id" => $tmp->rivi_id, "phase" => "tarkistus", "status" => 0);
                        callRest("POST", WEBROOT . "/rest/changeProsessingStatus.php", $data1, true);
                        continue;
                    }
                    echo "readCompleteFolders.php. $object_name ";
                    $message .= "\nHakemistossa on tiedostoja $filecount kpl. Tietokantaan tallennettu määrä: $added kpl. --> ";
                    if ($filecount > 0 && $tmp3 > 0 && $added == $filecount) {
                        $data3 = array("saveCollectionFileCount" => 1, "count" => $filecount, "object_name" => $object_name);
                        callRest("POST", WEBROOT . "/rest/editCollection.php", $data3);
                        $data1 = array("row_id" => $tmp->rivi_id, "phase" => "tarkistus", "status" => 2);
                        callRest("POST", WEBROOT . "/rest/changeProsessingStatus.php", $data1, true);
                    } else {
                        $message .= "Ongelmia tarkistuksessa:\n";
                        if ($filecount <= 0) {
                            $error = true;
                            $message .= "---- Hakemistosta ei löydy tiedostoja\n";
                        }
                        if ($tmp3 <= 0) {
                            $error = true;
                            $message .= "---- Aineistoa ei saatu lukittua\n";
                        }
                        if ($added != $filecount) {
                            $error = true;
                            $message .= "---- Tietokantaan ei saatu merkittyä kaikkia tiedostoja\n";
                        }
                        $data1 = array("row_id" => $tmp->rivi_id, "phase" => "tarkistus", "status" => 0);
                        callRest("POST", WEBROOT . "/rest/changeProsessingStatus.php", $data1, true);
                        $tmp_data = array("removeFiles" => 1, "file" => $file, "folder" => PICTURE_FOLDER . $folder, "row_id" => $tmp->rivi_id);
                        $removed = callRest("POST", WEBROOT . "/rest/files.php", $tmp_data, true);
                        $message .= "Poistettu tiedostottaulusta $removed riviä ja merkitty aineisto uudestaan käsittelyyn-->\n";
                    }
                }
                /*
                  $message .= "##### Tarkistus on valmis. Kesto: " . lapTime() . " #####\n";
                  $message = writeLog($message, $folder, $error);
                  echo "DONE " . lapTime() . " \n";
                 *
                 */
            } else {
//            echo "ei tarkisteta $folder \n";
                continue;
            }
        }
    }
}