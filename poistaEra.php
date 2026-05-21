<?php

if (!defined('ROOT')) {
    define('ROOT', dirname(__FILE__) . '/');
}
require_once ROOT . "settings.php";
require(ROOT . 'controllers/locales.php'); //Always the last
$lista_id = null;
$ei_poisteta = false;
$maara = 0;
if (isset($_GET["lista_id"])) {
    $lista_id = $_GET["lista_id"];
}

if ($lista_id < 0 || !is_numeric($lista_id)) {
    die("ei löydy tietoja");
}


$data = array("oneJob" => 1, "lista_id" => $lista_id);
$tmp = callRest("POST", "/rest/getJobs.php", $data, true);

foreach ($tmp as $rivi) {
    $maara++;
    if ($rivi->valmistunut != "") {
        $ei_poisteta = true;
    }
}
if ($ei_poisteta == true) {
    echo "Ei poisteta";
    die();
}
echo "Listassa on yhteensä $maara objektia. Joista ei ole valmistunut yhtään. <br />\n";

foreach ($tmp as $rivi) {
    if ($rivi->lista_id != $lista_id) {
        die("LISTA ID on väärin!");
    }
    $listan_rivi_id = $rivi->rivi_id;
    $sql = "DELETE FROM tyo_statukset WHERE listan_rivi_id =$listan_rivi_id; \n";
    $sql .= "DELETE FROM tyot WHERE listan_rivi_id =$listan_rivi_id; \n";
    $sql .= "DELETE FROM tiedostot WHERE listan_rivi_id =$listan_rivi_id; \n";
    $sql .= "DELETE FROM listan_rivit WHERE rivi_id =$listan_rivi_id; \n";

    echo $sql;
}
echo "DELETE FROM listat WHERE lista_id =$lista_id; \n";

file_put_contents("poista_lista_$lista_id.sql", $sql);
