<?php

if (file_exists(__DIR__ . "/../settings.php")) {
    require_once __DIR__ . "/../settings.php";
} else {
    die(":/ \n");
}
define("TRASH_FOLDER", "/kuvat/zzz_poistettavat/");
$pictures = TRASH_FOLDER;
$time = time() - 3600 * 14;
$msg = "Hakemisto $pictures. Selataan ... \n";
$folders = array_slice(array_filter(scandir($pictures)), 2);
if (count($folders) > 0) {
    foreach ($folders as $folder) {
        echo $msg;
        timerStart();
        $msg = "Hakemisto $folder --> ";
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
            continue;
        }
        $continue = true;
        foreach ($tmp as $row) {
            if ($row->valmis == 2 && $row->lahetetty == 2) {
                $msg .= "valmis ja lähetetty. ";
                break;
            } else {
                $msg .= $row->tiedosto . " Valmis: " . $row->valmis . ". Lähetetty: " . $row->lahetetty . " ... ";
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
        $msg .= "hakemiston voisi poistaa.\n";
//        $msg .= lapTime() . " \n";
    }
}