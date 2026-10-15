<?php

//Jos tarkistetaan zzz_hakemistoa, niin poistetaan. Muussa tapauksessa siirretään zzz hakemistoon

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
//    die();
}
$saanto = -1;
$pids = (int) shell_exec("ps ax | grep 'php " . CRON_FOLDER . "moveTo.php' | grep -v 'grep' | wc -l");
if ($pids > 1) {
    //echo shell_exec("ps ax | grep 'php " . CRON_FOLDER . "readCompleteFolders.php' | grep -v 'grep'");
    echo "moveTo.php on jo käynnissä. $pids ";
    die();
}

//$pictures = "/kuvat/zzz_nama_poistuisivat/";
$pictures = PICTURE_FOLDER;
//$folders = array_slice(array_filter(scandir(PICTURE_FOLDER)), 2);
//Museovirasto haluaa tiedostot suoraan verkkolevyn juureen. Tällä lajitellaan tiedostot hakemistoihin.

if ($pictures == "/kuvat/zzz_nama_poistuisivat/") {
    $remove = true;
    $time = time() - 3600 * 14;
} else {
    $remove = false;
    $time = 1640995200; //Vuoden 2022 alusta 
}
echo "Hakemisto $pictures. Selataan ... Poisto: $remove \n";
$folders = array_slice(array_filter(scandir($pictures)), 2);
if (count($folders) > 0) {
    foreach ($folders as $folder) {
        echo "Hakemisto $folder --> ";
        $data["canRemove"] = 1;
        $data["folder"] = "/kuvat/$folder/";
        $modified = filectime($pictures . $folder);
        if ($modified >= $time) {
            echo "Hakemisto on liian tuore: " . date("d.m.Y H:i", $modified) . "\n";
            continue;
        } else {
            echo "Hakemisto on luotu: " . date("d.m.Y H:i", filectime($pictures . $folder)) . " --> ";
        }
        $tmp = callRest("POST", "/rest/canRemove.php", $data, true);

        if (!isset($tmp[0])) {
            echo " **** ei löytynyt **** \n";
            file_put_contents($pictures . "hakemistoja-ei-loydy", "Hakemistoa $folder ei löydy tietokannasta\n");
            continue;
        }
        $continue = true;
        foreach ($tmp as $row) {
            if ($row->valmis == 2 && $row->lahetetty == 2) {
                echo "ok.. ";
            } else {
                echo $row->tiedosto . " Valmis: " . $row->valmis . ". Lähetetty: " . $row->lahetetty . " ... ";
                //file_put_contents($pictures . $folder, "Hakemiston kaikkia tiedostoja ei ole siirretty M+ :" . $row->tiedosto);
                $continue = false;
                continue;
            }
            if (time() < strtotime($row->valmistunut . ' +14 days')) {
                echo "Valmistunut alle 14 päivää sitten\n";
                $continue = false;
                continue;
            }
        }
        //Hakemisto on tarkistettu. Nyt katsotaan mitä sille tehdään.
        if ($continue == false) { //Erän jossain tiedostossa ollut vientiongelmia. Tästä syystä ei siirretä /poisteta.
	echo "odottaa valmistumista \n";
            continue;
        }
        if ($remove == false) {
            echo "hakemisto $folder siirretään roskakoriin --> ";
	    shell_exec ("mv $pictures$folder /kuvat/zzz_nama_poistuisivat/");
            shell_exec("touch /kuvat/zzz_nama_poistuisivat/$folder");
            echo "siirretty\n";
        } else {
            echo "poistetaan hakemisto: $pictures$folder --> ";
//            shell_exec("rm $pictures$folder -rf");
            echo "poistettu \n";
        }
    }
}

