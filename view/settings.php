<?php

$msg = submitPopup();
if (isset($_POST["save_artist"])) { //add an artist
    $data = array(
        "addAnArtist" => 1,
        "name" => checkPost($_POST["name"]),
        "internal_id" => checkNumber($_POST["internal_id"]),
    );
    $return = callRest("POST", WEBROOT . "/rest/addNewArtist.php", $data, true);
    if ($return > 0) {
        $_SESSION["tallennus_ok"] = "Tekijä on tallennettu.";
        header("location: ./");
    }
} elseif (isset($_POST["change_artist_status"])) { //change artist status
    $data = array(
        "changeStatus" => checkNumber($_POST["artist_id"]),
        "active" => 0
    );
    $return = callRest("POST", WEBROOT . "/rest/artists.php", $data, true);
    if ($return > 0) {
        $_SESSION["tallennusvirhe"] = "Tekijä on poistettu.";
        header("location: ./");
    }
} elseif (isset($_POST["save_legal"])) { //add a legal
    $data = array(
        "add" => 1,
        "license" => checkPost($_POST["license"]),
        "internal_name" => checkPost($_POST["internal_name"]),
    );
    $return = callRest("POST", WEBROOT . "/rest/addNewLegal.php", $data, true);
    if ($return > 0) {
        $_SESSION["tallennus_ok"] = "Lisenssi on tallennettu.";
        header("location: ./");
    }
} elseif (isset($_POST["change_legal_status"])) { //Change license status
    $data = array(
        "changeStatus" => checkNumber($_POST["legal_id"]),
        "active" => 0
    );
    $return = callRest("POST", WEBROOT . "/rest/legals.php", $data, true);
    if ($return > 0) {
        $_SESSION["tallennusvirhe"] = "Lisenssin status on poistettu.";
        header("location: ./");
    }
} elseif (isset($_POST["save_legal_type"])) { //add a legalType
    $data = array(
        "add" => 1,
        "type_name" => checkPost($_POST["legal_type"]),
        "internal_name" => checkPost($_POST["internal_name"]),
    );
    $return = callRest("POST", WEBROOT . "/rest/addNewLegalType.php", $data, true);

    if ($return > 0) {
        $_SESSION["tallennus_ok"] = "Oikeustyyppi on tallennettu.";
        header("location: ./");
    }
} elseif (isset($_POST["change_legaltype_status"])) { //Change type status
    $data = array(
        "changeStatus" => checkNumber($_POST["type_id"]),
        "active" => 0
    );
    $return = callRest("POST", WEBROOT . "/rest/legalTypes.php", $data, true);
    if ($return > 0) {
        $_SESSION["tallennusvirhe"] = "Oikeustyyppi status on poistettu.";
        header("location: ./");
    }
}

//LEGAL
//License == okeuden tyyppi
//Legal == oikeus
//ARTIST
$data1 = array("getArtists" => 1);
$tmp_artist = callRest("POST", WEBROOT . "/rest/artists.php", $data1, true);
$artist = "<form class='needs-validation' method='post' action='' novalidate>\n";
$artist .= "<label for='new_artist'>" . text("add a new author") . "</label>\n";
$artist .= "<div class='form-row'>\n";
$artist .= "    <div class='form-group col-md-6'>\n";
$artist .= "      <label for='inputName' class='two_rows'>" . text("author") . "</label>\n";
$artist .= "      <input type='name' class='form-control' name='name' id='inputName' placeholder='" . text("author") . "...' required>\n";
$artist .= "      <div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
$artist .= "    </div>\n";
$artist .= "    <div class='form-group col-md-6'>\n";
$artist .= "      <label for='inputInternalId' class='two_rows'>Museumplus yhteystieto moduulin id</label>\n";
$artist .= "      <input type='text' class='form-control' name='internal_id' id='inputInternalId' placeholder='Moduulin id...' required>\n";
$artist .= "      <div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
$artist .= "    </div>\n";
$artist .= "    <div class='form-group col-md-12'>\n";
$artist .= "  <button type='submit' class='btn btn-success text-right' name='save_artist'>" . text("save") . "</button>\n";
$artist .= "    </div>\n";
$artist .= "</form>\n";
$selection_artist = makeDropDownFromArray($tmp_artist, "artist", "artist_id", "change_artist_status", 10, "", false, "settings");
$msg .= siteBox("Liitetiedoston tekijätieto", $artist, $selection_artist);
//ARTIST stops
//Legals
$data2 = array("getLegals" => 1);
$data3 = array("getLegalTypes" => 1);
$tmp_legal = callRest("POST", WEBROOT . "/rest/legals.php", $data2, true);
$tmp_legal_types = callRest("POST", WEBROOT . "/rest/legalTypes.php", $data3, true);
$input1 = "<form class='needs-validation' method='post' action='' novalidate>\n";
$input1 .= "<label for='new_artist'>" . text("add a legal") . "</label>\n";
$input1 .= "<div class='form-row'>\n";
$input1 .= "    <div class='form-group col-md-6'>\n";
$input1 .= "      <label for='inputLicense' class='two_rows'>" . text("legal") . "</label>\n";
$input1 .= "      <input type='name' class='form-control' name='license' id='inputLicense' placeholder='" . text("legal") . "...' required>\n";
$input1 .= "      <div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
$input1 .= "    </div>\n";
$input1 .= "    <div class='form-group col-md-6'>\n";
$input1 .= "      <label for='inputInternalName' class='two_rows'>Museumplussan termin id</label>\n";
$input1 .= "      <input type='text' class='form-control' name='internal_name' id='inputInternalName' placeholder='Moduulin id...' required>\n";
$input1 .= "      <div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
$input1 .= "    </div>\n";
$input1 .= "    <div class='form-group col-md-12'>\n";
$input1 .= "  <button type='submit' class='btn btn-success text-right' name='save_legal'>" . text("save") . "</button>\n";
$input1 .= "    </div>\n";
$input1 .= "    </div>\n";
$input1 .= "</form>\n";
$input1 .= "<form class='needs-validation' method='post' action='' novalidate>\n";
$input1 .= "<label for='new_legal'>" . text("add a legal type") . "</label>\n";
$input1 .= "<div class='form-row'>\n";
$input1 .= "    <div class='form-group col-md-6'>\n";
$input1 .= "      <label for='inputLegal' class='two_rows'>Valitse oikeuden tyyppi esim. näyttökuva</label>\n";
$input1 .= "      <input type='name' class='form-control' name='legal_type' id='inputLegal' placeholder='" . text("add a legal type") . "...' required>\n";
$input1 .= "      <div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
$input1 .= "    </div>\n";
$input1 .= "    <div class='form-group col-md-6'>\n";
$input1 .= "      <label for='inputInternalName' class='two_rows'>Museumplussan termin id</label>\n";
$input1 .= "      <input type='text' class='form-control' name='internal_name' id='inputInternalName' placeholder='Moduulin id...' required>\n";
$input1 .= "      <div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
$input1 .= "    </div>\n";
$input1 .= "    <div class='form-group col-md-12'>\n";
$input1 .= "  <button type='submit' class='btn btn-success text-right' name='save_legal_type'>" . text("save") . "</button>\n";
$input1 .= "    </div>\n";

$input1 .= "</form><hr>\n";
$selections1 = makeDropDownFromArray($tmp_legal, "license", "legal_id", "change_legal_status", 10, "Oikeuksien hallinta", false, "settings");
$selections2 = makeDropDownFromArray($tmp_legal_types, "type", "type_id", "change_legaltype_status", 10, "Oikeuden tyypin hallinta", false, "settings");
$msg .= siteBox("Liitetiedoston oikeudet", $input1, $selections1 . $selections2);
//Legals stops

echo "<div class='row'>\n";
echo $msg;
echo "</div>";

function siteBox($title, $inputs, $selections) {
    $msg = "<div class='col-lg-6 mb-4'> ";
    $msg .= "   <div class='card shadow mb-4'>\n";
    $msg .= "       <div class='card-header py-3 no_pdf'>\n";
    $msg .= "           <h6 class='m-0 font-weight-bold text-primary'>$title</h6>\n";
    $msg .= "       </div>\n";
    $msg .= "       <div class='card-body'>$inputs</div>\n";
    $msg .= "   <form method='post' action=''>\n";
    $msg .= $selections; //Forms are included...
    $msg .= "       </form>\n";
    $msg .= "       </div>\n";
    $msg .= "   </div>\n";
    $msg .= "</div>\n";
    return $msg;
}
