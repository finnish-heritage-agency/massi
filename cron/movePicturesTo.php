<?php

/* 14.9.2022
 * Sovellukselle annettaan parametri trash = Tällöin siivotaan trash hakemisto
 * Muussa tapauksessa siivotaan PICTURE_FOLDER hakemistosta onnistuneet TRASH hakemistoon
 * Jos tarkistetaan zzz_hakemistoa, niin poistetaan. Muussa tapauksessa siirretään zzz hakemistoon
 * Tällä siivotaan pictures hakemistosta onnistuneet tiedostot ns. trash hakemistoon (zzz_poistuvat)
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
$pids = (int) shell_exec("ps ax | grep 'php " . CRON_FOLDER . "movePicturesTo.php' | grep -v 'grep' | wc -l");
if ($pids > 1) {
    echo "movePicturesTo.php on jo käynnissä. $pids ";
    die();
}
define("TRASH_FOLDER", "/kuvat/zzz_poistettavat/");
if (!is_numeric(SAVE_LOGS)) {
    $save = 999;
} else {
    $save = SAVE_LOGS;
}
if (isset($argv[1]) && $argv[1] == "trash") {
    $pictures = TRASH_FOLDER;
    $remove = true;
    $time = time() - 3600 * $save;
} else {
    $pictures = PICTURE_FOLDER;
    $remove = false;
//    $time = 1640995200; //Vuoden 2022 alusta. Oli kertasiivous
    $time = time() - 3600 * $save;
}


$msg = "Hakemisto $pictures. Selataan ... \n";
$folders = array_slice(array_filter(scandir($pictures)), 2);
if (count($folders) > 0) {
    foreach ($folders as $folder) {
        if ($folder == "zzz_poistettavat") {
            continue;
        }
        timerStart();
        $msg .= "Hakemisto $folder --> ";
        $data["canRemove"] = 1;
        $data["folder"] = "/kuvat/$folder/";
        $modified = filectime($pictures . $folder);
        if ($modified >= $time) {
            $msg .= "Hakemisto on liian tuore: " . date("d.m.Y H:i", $modified) . "\n";
            continue;
        } else {
            $msg .= "Hakemisto on luotu: " . date("d.m.Y H:i", filectime($pictures . $folder)) . " --> ";
        }
        $tmp = callRest("POST", WEBROOT . "/rest/canRemove.php", $data, true);

        if (!isset($tmp[0])) {
            $msg .= " **** hakemistoa ei löytynyt tietokannasta. **** \n";
            //Tämä oli vain testiä varten. Ei viedä tuotantoon.
//            file_put_contents($pictures . "hakemistoja-ei-loydy", "Hakemistoa $folder ei löydy tietokannasta\n");
            continue;
        }
        $continue = true;
        foreach ($tmp as $row) {
            if ($row->valmis == 2 && $row->lahetetty == 2) {
                $msg .= "ok.. ";
            } else {
                $msg .= $row->tiedosto . " Valmis: " . $row->valmis . ". Lähetetty: " . $row->lahetetty . " ... ";
                //file_put_contents($pictures . $folder, "Hakemiston kaikkia tiedostoja ei ole siirretty M+ :" . $row->tiedosto);
                $continue = false;
                continue;
            }
            if (time() < strtotime($row->valmistunut . ' +14 days')) {
                $msg .= "Valmistunut alle 14 päivää sitten\n";
                $continue = false;
                continue;
            }
        }
        //Hakemisto on tarkistettu. Nyt katsotaan mitä sille tehdään.
        if ($continue == false) { //Erän jossain tiedostossa ollut vientiongelmia. Tästä syystä ei siirretä /poisteta.
            $msg .= "odottaa valmistumista \n";
            continue;
        }
        if ($remove == false) {
            $msg .= "hakemisto $folder siirretään roskakoriin --> ";
            shell_exec("mv $pictures$folder " . TRASH_FOLDER);
            shell_exec("touch " . TRASH_FOLDER . "$folder");
            $msg .= "hakemisto siirretty roskakoriin. ";
        } else {
            $msg .= "poistetaan hakemisto: $pictures$folder --> ";
            shell_exec("rm $pictures$folder -rf");
            $msg .= "poistettu. ";
        }
        $msg .= lapTime() . " \n";
    }
}
echo $msg;
