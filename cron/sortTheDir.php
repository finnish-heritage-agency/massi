<?php

/**
 * 10.5.2022 JREPO
 * Huomattu, että lajittelussa on välillä todella paljon tiedostoja. Tätä ajetaan myös päivisin...
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
$saanto = -1;
$pids = (int) shell_exec("ps ax | grep 'php " . CRON_FOLDER . "sortTheDir.php' | grep -v 'grep' | wc -l");
if ($pids > 1) {
    //echo shell_exec("ps ax | grep 'php " . CRON_FOLDER . "readCompleteFolders.php' | grep -v 'grep'");
    echo "sortTheDir.php on jo käynnissä. $pids ";
    die();
}
$folders = array_slice(array_filter(scandir(PICTURE_FOLDER)), 2);
//Museovirasto haluaa tiedostot suoraan verkkolevyn juureen. Tällä lajitellaan tiedostot hakemistoihin.

if (count($folders) > 0) {
    foreach ($folders as $file) {
        if (!is_dir(PICTURE_FOLDER . $file)) {
            $modified = filectime(PICTURE_FOLDER . $file) - 60 * 5;
            if ($modified >= time()) {
                $stop_process = true;
            } else {
                $folder = basename($file);
                $delimeters = explode("_", $folder);
                $dots = substr_count($folder, ".");

                if ($dots >= 2) { //15.9.2022 Palvelupyyntö3
                    $saanto = 5;
//                    $type = pathinfo($folder, PATHINFO_EXTENSION);
                    $type = pathinfo($folder);
                    $folder = str_replace(":", "_", $type["filename"]);
                    //$folder = str_replace(".", "_", $folder);
                } elseif (strpos($folder, "___") !== false) {//Jos objektinnimi päättyy: ja sisältää useamman tiedoston, joiden nimessä __.
                    $saanto = 4;
                    $delimeters = explode("_", $folder);
                    $folder = $delimeters[0] . "_";
                } elseif (strpos($folder, "__") !== false) {
                    //Possible file KC3011__KC3011a.tif & KYP410489_.tif & KYP410489___2.dng:
                    $saanto = 3;
                    $delimeters = explode("__", $folder);
                    $folder = $delimeters[0];
                } else if (count($delimeters) > 2) { // file HK7155_1352_61_6.tif:
                    $saanto = 1;
                    $folder = $delimeters[0] . "_" . $delimeters[1];
                    if (isset($delimeters[2])) {
                        if (strpos($delimeters[2], ".") !== false) {
                            $saanto = "1 A";
                            $tmp = explode(".", $delimeters[2]);
                            $end = $tmp[0];
                            $folder .= "_" . $end;
                        }
                    }
                } else { //Possible file HK6000_1556.tif
                    $tmp_folder = explode(".", $folder);
                    if (count($tmp_folder) > 1) {
                        $saanto = 2;
                        $folder = $tmp_folder[0];
                    }
                }
                if (!file_exists(PICTURE_FOLDER . $folder)) {
                    mkdir(PICTURE_FOLDER . $folder, 0775, false);
                }
                if (!file_exists(PICTURE_FOLDER . $folder)) {
                    $message = "Cant make folder...";
                    writeLog($message, $folder);
                    echo $message;
                    die($message);
                }
                $command = "mv " . PICTURE_FOLDER . $file . " " . PICTURE_FOLDER . "$folder ";
                shell_exec($command);
                echo "Siirretään tiedosto $file hakemistoon $folder | Sääntö: $saanto | $command\n";
            }
        }
    }
}
if ($stop_process == true) {
    $message = "Still coming data...";
    die($message);
}
