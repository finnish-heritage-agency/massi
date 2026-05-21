<?php

/**
 * Tällä käskytetään muita croneja
 */
if (!isset($_SERVER["SHELL"])) { //Via browser
    header("HTTP/1.0 403 Forbidden");
    die();
}
if (file_exists(__DIR__ . "/../settings.php")) {
    require_once __DIR__ . "/../settings.php";
} else {
    die(":/ \n");
}
$pids = (int) shell_exec("ps ax | grep 'php " . CRON_FOLDER . "mainCron.php' | grep -v 'grep' | wc -l");
//$pids = shell_exec("ps ax | grep 'php " . CRON_FOLDER . "mainCron.php' | grep -v 'grep'");
if ($pids > 1) {
    die("maincron käynnissä");
}

$ok = callRest("POST", "/rest/checkMplusAttempts.php", array("check" => 1), true);

if (file_exists(SOFTWARE_BREAK) || $ok != 1) {
    if (!file_exists(SOFTWARE_BREAK)) {
        touch(SOFTWARE_BREAK); //Tulee katko näkyviin myös edustapalvelulle ja tätä kautta myöskin kaikki muut siirrot pysähtyvät
        shell_exec("echo $ok > " . SOFTWARE_BREAK);
        shell_exec("chown apache. " . SOFTWARE_BREAK);
    }
    die("katko. Syy: $ok");
}

$date = date("d-m-Y", time());
$x = shell_exec('whoami');
$x = preg_replace('/\s+/', '', $x);
$msg = "";
if ($x != ROOT_USER) {
    die("Väärä käyttäjä");
}

/* Tarkistetaan PICTURE_FOLDER hakemisto
 * Tarkistetaan onko tietokannassa valmiita rivejä.
 * Jos löytyy, niin katsotaan löytyykö hakemistolle objektiId:tä M+ järjestelmästä
 */

/*
 * Lajitellaan hakemistoa...
 */
$x = shell_exec("php " . CRON_FOLDER . "sortTheDir.php");
$x = writeLog($x, "cron");

$x = shell_exec("php " . CRON_FOLDER . "readCompleteFolders.php");
$x = writeLog($x, "cron");

if (clockBetween() == 1) {
    echo "Ajetaan... ";
    $x = shell_exec("php " . CRON_FOLDER . "prosessingFolders.php");
    $x = writeLog($x . "\n", "cron");
    echo "Prosessointi on valmis... ";
    $x = shell_exec("php " . CRON_FOLDER . "changeThumbnailBoo.php");
    $x = writeLog($x . "\n", "cron");
    echo "Thumbit heitetty... ";
}

if (clockBetween("05:00", "05:05") == 1) {
    /* Poistetaan vanhat lokitiedostot */
    $x = shell_exec("php " . CRON_FOLDER . "removeOldLogs.php");
    //$x = writeLog($x . "\n", "cron-$date");
    if (REMOVE_READY_FOLDER == 1) {
        $x = shell_exec("php " . CRON_FOLDER . "movePicturesTo.php trash"); //Siivotaan pictures hakemisto
        $x = writeLog($x . "\n", "PicturesToTrash");
        $x = shell_exec("php " . CRON_FOLDER . "movePicturesTo.php"); //siivotaan roskakori
        $x = writeLog($x . "\n", "removingPictures");
    }
}
