<?php

/** SOFTREPO 1.7.2020
 * Using from frontend and backend...
 */
set_time_limit(0); // to infinity for
DEFINE("UPDATED", "27.11.2022");
DEFINE("OLD_COLLECTIONS", "2022-05-13");
date_default_timezone_set('Europe/Helsinki');
if (!defined('ROOT')) {
    define('ROOT', dirname(__FILE__) . '/');
}
$config = shell_exec("hostname");
$config_file = preg_replace('/\s+/', '', $config);
require_once ROOT . "$config_file.php";
$version = "0.1"; //If needs to reload css files...
$virtuaali = false; //Vaikuttaa nfs levyyn

if (!isset($_SERVER["SHELL"])) { //if is not cron job...
    define("PDF_ACCESS_URL", $_SERVER["SERVER_NAME"]); //Mikä url on hyväksytty
    if (!defined('WEBROOT')) {
        define('WEBROOT', $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"]);
    }
} else {
    if (HTTPS_ONLY == true) {
        header("location: https://" . $_SERVER['HTTP_HOST'] . "" . $_SERVER['REQUEST_URI']);
    }
}

require_once(ROOT . 'controllers/functions.php');
require_once(ROOT . 'controllers/html_functions.php');
require_once(ROOT . 'models/pdo_functions.php');
spl_autoload_register(function ($class_name) {
    include ROOT . "oop/$class_name" . ".php";
});

timerStart();

/*
 * status tiedot numeerissa muodossa
 * 1 = valmis
 * 0 = otettu jonoon
 * 9 & 99= käsittelyssä
 * 98 = Jo digitoitu
 * -1
 */

//filetypes for M+ service...
$text = array("csv", "epub", "xhtml", "xml", "html", "odf", "txt", "pdf");
$voice = array("aiff", "bwf", "flac", "lpcm", "aac", "wav", "mpeg", "mp3", "wma");
$moved_pictures = array("dpx", "ffvi", "avc", "dv", "mpeg", "wmv", "mov", "mp4");
$image = array("dng", "jpg", "jpeg", "jp2", "png", "svg", "tif", "tiff", "eps", "gif");
$three_d = array("obj", "mtl", "gltf", "glb", "stl");
$others = array("zip");
$types = array("3D" => $three_d, "Image" => $image, "Moved images" => $moved_pictures, "Voice" => $voice, "Text" => $text, "Others" => $others);
DEFINE("TYPES", $types);

if (isset($argv[1]) && $argv[1] == "check") {
    $phpt = array(
        "php-mbstring",
        "php-gd",
        "php-7",
        "php-pdo",
        "php-json",
        "php-mysqlnd",
        "php-xml",
    );
    $who = exec("whoami");
    if ($who != ROOT_USER) {
//        die("Ajettava " . ROOT_USER . " käyttäjänä\n");
    }
    echo "Laitetaan hakemistot oikeaan omistukseen: \n";
    if (!file_exists(LOGS)) {
        shell_exec("mkdir -p " . LOGS);
    }
    if (!file_exists(CRON_FOLDER)) {
        shell_exec("mkdir -p " . CRON_FOLDER);
    }
    if (!file_exists(ROOT . "rest/logs/")) {
        shell_exec("mkdir -p " . ROOT . "rest/logs/");
    }
    shell_exec("chmod +x " . CRON_FOLDER . "* -R");
    shell_exec("chown massadigitointi. " . CRON_FOLDER . "* -R");
    shell_exec("chown apache.massadigitointi " . TMP . " -R");
    shell_exec("chmod 755 " . TMP . " -R");
    if (!file_exists(PICTURE_FOLDER)) {
        shell_exec("mkdir -p " . PICTURE_FOLDER);
    }
    shell_exec("chown apache. " . PICTURE_FOLDER);
    shell_exec("chmod -v 1777 /tmp /var/tmp");
    echo "Tarkistetaan sovellukset: \n";
    echo "EXIFTOOL: ";
    if (file_exists(EXIFTOOL)) {
        echo "OK";
    } else {
        echo "Ei löydy";
    }
    echo "\n";
    $x = shell_exec("gettext testi");
    echo "GETTEXT: ";
    if ($x == "testi") {
        echo "OK";
    } else {
        echo "Ei löydy";
    }
    echo "\n";
    $x = shell_exec("rpm -qa | grep php");
    foreach ($phpt as $row) {
        if (strpos($x, $row) !== false) {
            echo "$row: OK\n";
        } else {
            echo "$row: Ei löydy\n";
        }
    }
    echo "Aja tietokantaan ALTER TABLE `listat` ADD `eran_tallentaja` varchar(30) NULL DEFAULT '' AFTER `finna`; #14.9.2022";
}

if (!is_numeric(RE_TRIES)) {
    die("Yritysten määrä pitää olla numeerinen!");
}

//05.2022
/*
ALTER TABLE `listan_rivit` ADD INDEX `valmis` (`valmis`);
ALTER TABLE `tyot` ADD INDEX `listan_rivi_id` (`listan_rivi_id`);
ALTER TABLE `listan_rivit` ADD INDEX `lista_id` (`lista_id`);
 */
