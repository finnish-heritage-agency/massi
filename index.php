<?php

/**
 * htaccess tiedostossa tehdään http --> https
 */
if (!defined('ROOT')) {
    define('ROOT', dirname(__FILE__) . '/');
}
require_once ROOT . "settings.php";
require(ROOT . 'controllers/locales.php'); //Always the last
global $version;
session_start(); //After OOP requires
showErrors(0);
$folder = "view/";
$pages = array(//if The site doesn not found here, 404 will show
    'etusivu' => $folder . "frontpage.php",
    'oletusarvot' => $folder . "default_values.php",
    'viivakoodi' => $folder . "barCode.php",
    'kasittelyssa' => $folder . "handling.php",
    'uusiEra' => $folder . "new_collection.php",
    'erat' => $folder . "collections.php",
//    'erat' => $folder . "new_collections.php",
    'era' => $folder . "collection.php",
    'lokit' => $folder . "logs.php",
    'siirrot' => $folder . "all_transfers.php",
    'asetukset' => $folder . "settings.php",
    'eranLokit' => $folder . "checkCollectionLogs.php",
//    'testi' => $folder . "testi.php",
);
//sidebar links (not dropdowns)
$links = array(
    array("name" => text("technical settings"), "url" => "oletusarvot", "ico" => "fa-cogs"),
//    array("name" => text("pending"), "url" => "kasittelyssa", "ico" => "fa-file-pdf"),
    array("name" => text("technical logs"), "url" => "lokit", "ico" => "fa-flag"),
//    array("name" => text("settings"), "url" => "asetukset", "ico" => "fa-cogs"),
//    array("name" => text("all transfers"), "url" => "siirrot/refresh", "ico" => "fa-exchange-alt"),
);

$collection = array(
    array("name" => text("new") . " " . text("digitization batch", 3), "url" => "uusiEra", "ico" => "fa-archive"),
    array("name" => text("digitization batchs"), "url" => "erat", "ico" => "fa-archive"),
);

//Muista viedä samat myös valid funktioon
$langs = array("fi_FI" => "Suomi", "en" => "Englanti", "swe" => "Ruotsi");

//Just checking....
if (isset($_GET["debug"])) {
    echo"Debugging requires | ";
    echo text("test") . " "; //Check is gettext installed
    echo "| Check BarCode --> ";
    if (!extension_loaded('imagick')) {
        echo "Imagick is not installed";
    } else {
        echo "Imagick is OK";
    }
    writeLog("test writing in file");
}

//SITE
if (isset($_GET ['page']) && $_GET ['page'] != "") {
    $selected_site = $_GET ['page'];
} else {
    $selected_site = "etusivu";
}
$site = htmlStart($selected_site, $version);

if ($selected_site != "eranLokit") {
    $site .= htmlSidebar();
}
$site .= getUrlPage($selected_site);
$site .= htmlStop($selected_site);
echo $site;
