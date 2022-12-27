<?php

$tmp = array();
$data = null;
$virhe = null;
if (isset($_POST["new_collection"])) {
    $title = checkPost($_POST["title"]);
    if (isset($_FILES) && $_FILES["fileToUpload"]["tmp_name"] != "") {
        $file = $_FILES;
        $checked = checkFile($file);
        if ($checked == 1) {
            $tmp_parser = file_get_contents($file["fileToUpload"]["tmp_name"]);
            $tmp_parser = str_replace("\n", " ", $tmp_parser); //CSV make lines in seperate rows
            $tmp_parser = utf8_encode($tmp_parser);
        }
        $tmp_parser .= $_POST["rows"];
        $tmp_parser = preg_replace("/\r|\n/", "", $tmp_parser);
        $_POST["rows"] = $tmp_parser;
    } else {
        $tmp_parser = $_POST["rows"]; //can´t use checkpost :/
    }

    $row = 0;
    foreach (PARSER as $parser) {
        $row++;
        $tmp_parser = str_replace("$parser", "  ", $tmp_parser);
        //$tmp_parser = str_replace(";", "", $tmp_parser);
    }
    $tmp = explode("  ", $tmp_parser);
    //19.2.2021. Haluttu tarkistus, onko objekti jo digitoitu
    $tmp_data = array("checkCollectionsRows" => 1, "rows" => $tmp_parser);
    $data = callRest("POST", WEBROOT . "/rest/collections.php", $tmp_data, true); // check is file_object_id generated already
}

$message = submitPopup();

$content = showParsers() . "<input type='text' placeholder='Otsikko...' name='title' class = 'form-control col-md-12' required>";
$content .= "<div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
/*
  $content .= "<input type='text' placeholder='Uudet rivit...' name='rows' class = 'form-control col-md-12' max='" . DATA_LENGTH . "' required>";
  $content .= "<div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
 *
 */
$content .= "<input type='text' placeholder='Uudet rivit...' name='rows' class = 'form-control col-md-12' max='" . DATA_LENGTH . "'>";

$content .= "<p>Tai pudota CSV tiedosto tähän.<br />Excel --> Vie --> Muuta tiedostotyyppi --> CSV (luetteloerotin)</p>\n";
$content .= "<input type='file' class='dropify' name='fileToUpload' id='fileToUpload'/>\n";
//$modal = makeModalView("Uusi listaus", "new_collection", "Tuo listaus", "./", $content, "", "btn-success text-right");

$message .= "<div class='row'>";
if ($data != "") {
    $data1 = array("getArtists" => 1, "active" => 1);
    $tmp_artist = callRest("POST", WEBROOT . "/rest/artists.php", $data1, true);
    $data2 = array("getLegals" => 1, "active" => 1);
    $tmp_license = callRest("POST", WEBROOT . "/rest/legals.php", $data2, true);
    $data3 = array("getLegalTypes" => 1, "active" => 1);
    $tmp_type = callRest("POST", WEBROOT . "/rest/legalTypes.php", $data3, true);
    $message .= "<form class='col-md-12' action='" . WEBROOT . "/sivu/erat/' method='post' autocomplete='off'>";
    $message .= "<div class='row'>\n";
    $message .= makeDropDownFromArray($tmp_artist, "artist", "artist_id", false, 4, "Tekijä", false, "new_era");
    $message .= makeDropDownFromArray($tmp_license, "legal", "legal_id", false, 4, "Oikeus", false, "new_era");
    $message .= makeDropDownFromArray($tmp_type, "type", "type_id", false, 4, "Oikeustyyppi", false, "new_era");
    $message .= "<div class='col-md-12'><br />\n";
    $message .= "<div class='form-check'>\n";
    $message .= "   <input type='checkbox' name='finna' id='finna' value='1'>\n";
    $message .= "   <label class='form-check-label' for='inlineRadio1'>Digitointierän tiedostot julkaistaan Finnassa</label>\n";
    $message .= "<div class='row'>\n";
    $message .= "   <div class='offset-8 col-md-4'>\n";
    $message .= "       <label for='title' class='text-dark'>Erän tallentaja (pakollinen, min 5 merkkiä)</label>\n";
    $message .= "       <input type='text' placeholder='Erän tallentaja' name='batch_saver' class ='form-control' minlength='5' required>";
    $message .= "       <input type='submit' name='add_collection' Value='Tallenna listaus'  class = 'btn btn-success text-right'>";
    $message .= "       </div>\n";
    $message .= "   </div>\n";
    $message .= "</div>\n";

    $message .= "</div>\n";

    $message .= "</div>\n";
    $message .= "<div class='col-md-12'><h2 class='text-center'> $title  </h2></div>\n";
    $message .= "<table class='table table-bordered text-center' width='100%'>\n";
    $message .= "   <thead><tr><th>Objekti</th><th>Hakemisto</th><th>Viivakoodi</th></tr></thead>";
    $message .= "    <tbody>\n";

    foreach ($data as $tmp_row) {
        $row = stripslashes($tmp_row->collection);
        if ($row == "") {
            continue;
        }
        $folder = str_replace(":", "_", $row);
        //26.9.2022 Halutaan hakemistoon jättää pistenimi
        //$folder = str_replace(".", "_", $folder); //20.5
        if ($folder != "") {
            $data = array(
                "type" => "jpg",
                "text" => $folder, // pitää olla aika pitkä tai ei tulostu
                "code" => "TYPE_CODE_128",
            );
            $barcode = callRest("POST", WEBROOT . "/rest/barCodeGenerator.php", $data);
        } else {
            $barcode = "";
        }
        if ($tmp_row->digitized == 1) {
            $color = "class='table-danger'";
            $title = "title='Objekti on jo digitoitu'";
        } else {
            $color = "";
            $title = "";
        }
        $message .= "       <tr $color $title>\n";
        $message .= "           <td>" . strip_tags($row) . "</td>\n";
        $message .= "           <td>" . strip_tags($folder) . "</td>\n";
        $message .= "           <td>$barcode<br />" . strip_tags($folder) . "</td>\n";
        $message .= "       </tr>\n";
    }
    $message .= "    </tbody>\n";
    $message .= "</table>\n";
    $message .= "<input type='hidden' name='title' Value='" . $_POST["title"] . "'>";
    $message .= "<input type='hidden' name='rows' Value='" . $_POST["rows"] . "'>";
    $message .= "</form>\n";
} else {
    $message .= "<div class='col-md-12'>" . $modal["button"] . "</div>\n";
}

$message .= "</div>";
echo makeCard(12, text("new") . " " . text("digitization batch", 3), $message, false);
?>