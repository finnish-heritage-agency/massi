<?php

$author = null;
$right = null;
$right_type = null;
$message = submitPopup();
if (BY_DIGITOINTIERA == false) {
    if (isset($_GET["rivi_id"])) { //Changing row status in Collection
        $progress = 0;
        if (isset($_GET["progress"]) && is_numeric($_GET["progress"])) {
            $progress = $_GET["progress"];
            $data = array(
                "changeCollectionRowStatus" => $_GET["rivi_id"],
                "progress" => $progress
            );
            $tmp = callRest("POST", WEBROOT . "/rest/editCollection.php", $data);
            if ($tmp != 1) {
                writeLog("COLLECTION ERROR 1, Kokoelman muokkaus ei onnistunut. Syy: $tmp");
            }
            if ($progress == 1) { //Let´s put file in job queue
                $data1 = array(
                    "newJob" => 1,
                    "row_id" => $_GET["rivi_id"],
                );
                $tmp = callRest("POST", WEBROOT . "/rest/addNewJob.php", $data1, true);
            }
            if (is_numeric($tmp)) {
                $_SESSION["tallennus_ok"] = "Aineisto siirretty työjonoon.";
                header("location: " . WEBROOT . "/sivu/era/" . $_GET["id"] . "/");
            } else {
                $message .= "<p class='error_red'>Aineistoa ei saatu siirrettyä jonoon (" . print_r($tmp, true) . ")</p>";
            }
        }
    }
}
if (isset($_POST["edit_collection"])) {
    if (isset($_POST["finna"]) && $_POST["finna"] != "") {
        $finna = $_POST["finna"];
    } else {
        $finna = 0;
    }
    if (isset($_POST["artist_id"]) && $_POST["artist_id"] != "") {
        $artist_id = $_POST["artist_id"];
    } else {
        $artist_id = 0;
    }
    if (isset($_POST["legal_id"]) && $_POST["legal_id"] != "") {
        $legal_id = $_POST["legal_id"];
    } else {
        $legal_id = 0;
    }
    if (isset($_POST["type_id"]) && $_POST["type_id"] != "") {
        $type_id = $_POST["type_id"];
    } else {
        $type_id = 0;
    }
    $id = $_POST["id"];
    if (is_numeric($finna) && is_numeric($artist_id) && is_numeric($legal_id) && is_numeric($type_id) && is_numeric($id)) {
        $data = array(
            "editCollectionValues" => 1,
            "finna" => $finna,
            "artist_id" => $artist_id,
            "license_id" => $legal_id,
            "type_id" => $type_id,
            "id" => $id,
        );
        $tmp = callRest("POST", WEBROOT . "/rest/collections.php", $data, true);
        if ($tmp == 1) {
            $_SESSION["tallennus_ok"] = "Tiedot on päivitetty";
            header("location: " . WEBROOT . "/sivu/era/" . $_GET["id"] . "/");
        }
    }
}


$data = array("getOneCollectionById" => $_GET["id"]);
$tmp = callRest("POST", WEBROOT . "/rest/collections.php", $data, true);
$collection = $tmp[0];
$tmp_rows = $tmp[1];
if ($tmp_rows[0]->valmis == 0) {
    $waiting = true;
    $more_title = "Erän siirtoa ei ole aloitettu";
} else {
    $waiting = false;
    $more_title = null;
}

$links = array(
    array("url" => "/sivu/erat/", "title" => text("digitization batchs")),
    array("url" => "/sivu/era/" . $_GET["id"] . "/", "title" => $collection->otsikko),
);

$bread = breadCrumb($links, $collection->otsikko, $more_title);
$title = $bread . " <span class='text-right'>" . date("d.m.Y H:i:s", strtotime($collection->paivays)) . " | " . $collection->eran_tallentaja . "</span><br />";
$url = WEBROOT . $_SERVER["REQUEST_URI"];
$message .= "<div class='row'>\n";
$message .= "<div class='col-md-4'>";
$message .= "<h4 class='no_pdf'>Julkaistaanko Finnassa: ";
if ($tmp[0]->finna == 1) {
    $message .= "<span class='success'><strong>" . changeReturn($tmp[0]->finna) . "</strong></span>";
} else {
    $message .= "<span class='disabled'><strong>" . changeReturn($tmp[0]->finna) . "</strong></span>";
}
if ($tmp[0]->tekija != "") {
    $message .= "<br /> Tekijä: " . $tmp[0]->tekija;
    $author = $tmp[0]->tekija;
} else {
    $message .= "<br /> Tekijä: -";
}
if ($tmp[0]->oikeus != "") {
    $message .= "<br /> Oikeus: " . $tmp[0]->oikeus;
    $right = $tmp[0]->oikeus;
} else {
    $message .= "<br /> Oikeus: -";
}
if ($tmp[0]->tyyppi != "") {
    $message .= "<br /> Oikeuden tyyppi: " . $tmp[0]->tyyppi;
    $right_type = $tmp[0]->tyyppi;
} else {
    $message .= "<br /> Oikeuden tyyppi: -";
}
$message .= "</h4></div>";
$message .= "<div class='col-md-8'>";

$message .= "<a href='" . WEBROOT . "/pdfgenerator/index.php?url=" . $url . "print' class='text-right no_pdf' target='_blank'><i class='fa fa-print' aria-hidden='true'></i> " . text("print") . "</a>";

if ($waiting == true) {
    $content = makeEditCollection($tmp[0]->finna, $tmp[0]->tekija_id, $tmp[0]->sisainen_oikeus, $tmp[0]->sisainen_tyyppi, $collection->lista_id);
    $modal = makeModalView("Muokkaa", "edit_collection", "Tallenna", "./", $content, "", "btn-outline-success");
    $message .= "<div class='no_pdf'>" . $modal["button"] . "</div>";
    $message .= $modal["message"];
    $message .= "<br /><button class='btn btn-sm btn-outline-success text-right no_pdf' aria-label='' onclick=\"varmistus(" . $collection->lista_id . ",'true')\">Aloita tiedostojen siirto</button>";
}

$message .= "</div>\n";
$message .= "</div>\n"; //ROW
if ($waiting == false && !isset($_GET["rivi_id"])) {
    $message .= "<div id='loading' class='text-center'></div>";
    $message .= "<div id='one_job' style='width: 100%;></div>"; //Refreshing site..
} else {
    $message .= "<table class='table table-bordered text-center'>\n";
    $message .= "   <thead><tr><th class='text-center'>Objekti</th><th class='text-center'>Viivakoodi</th>";
    if (BY_DIGITOINTIERA == false) {
        $message .= "<th class='text-center'>Valmiina</th>";
    }
    if ($waiting == false) {
        foreach (JOB_PHASES as $phase) {
            if ($phase == "lahetys") {
                $phase = "Lähetys";
            } elseif ($phase == "nayttokuvat") {
                $phase = "Näyttökuvat";
            }
            $message .= "           <th>" . ucfirst($phase) . "</th>\n";
        }
        $message .= "<th>Valmis</th>";
    }
    $message .= "</tr></thead>\n";
    $message .= "    <tbody>\n";
    foreach ($tmp_rows as $row) {
        $code = str_replace(":", "_", $row->kokoelmatunnus);
        //26.9.2022 Halutaan hakemistoon jättää pistenimi
        //$code = str_replace(".", "_", $code); //20.5
        $data = array(
            "type" => "svg", //Pitää olla svg, jos haluaa tulostukseen toimimaan
            "text" => $code,
            "code" => "TYPE_CODE_128",
        );
        $barcode = callRest("POST", WEBROOT . "/rest/barCodeGenerator.php", $data);
        $message .= "       <tr>\n";
        $message .= "           <td>" . $row->kokoelmatunnus . "</td>\n";
        if (BY_DIGITOINTIERA == false) {
            $message .= "           <td class='progress-$row->valmis'>$barcode</td>\n";
            if ($row->valmis == 0) {
                $message .= "           <td class='text-center'><a href='" . WEBROOT . "/sivu/era/" . $_GET["id"] . "/$row->rivi_id&progress=9' class='btn btn-info' title='Aloita käsittely'><i class='fas fa-check-square'></i> Aloita käsittely</a>";
                $message .= "</td>\n";
            } elseif ($row->valmis == 9) {
                $message .= "           <td class='text-center progress-$row->valmis'>Työnalla\n";
//        $message .= "<a href='" . WEBROOT . "/sivu/kokoelma/" . $_GET["id"] . "/$row->rivi_id&progress=1' class='btn btn-success text-right' title='Merkitse valmiiksi'><i class='fas fa-check-square'></i> Merkitse valmiiksi</a></td>\n";
                //DEMO
                $message .= "<a href='" . WEBROOT . "/sivu/era/" . $_GET["id"] . "/$row->rivi_id&progress=1&tunnus=" . $row->kokoelmatunnus . "' class='btn btn-success text-right' title='Merkitse valmiiksi'><i class='fas fa-check-square'></i> Merkitse valmiiksi</a></td>\n";
            } else {
                $message .= "           <td class='text-center'>Kyllä</td>\n";
            }
        } else {
            $message .= "           <td class='barcode_space'>$barcode\n";
            $message .= "<br />$code</td>\n";
        }
        if (isset($_GET["rivi_id"]) && $_GET["rivi_id"] == "print" && $waiting == false) {
            $data = array("getOneJobForLogs" => 1, "rivi_id" => $row->rivi_id);
            $tmp = callRest("POST", WEBROOT . "/rest/getJobs.php", $data, true);
            if (isset($tmp[0])) {
                $tmp = $tmp[0];
            }
            foreach (JOB_PHASES as $phase) {
                $message .= "           <td>" . showIcon($tmp->$phase, true) . "</td>\n";
            }
            $message .= "           <td>" . showIcon($tmp->rivi_valmis, true) . " ";
            if ($tmp->valmistunut != "") {
                $sorting = sortDate(date("d.m.Y H:i:s", strtotime($tmp->valmistunut)));
                $message .= $sorting["sort"];
                $message .= $sorting["day"];
            }
            $message .= "</td>\n";
        }

        $message .= "       </tr>\n";
    }
    $message .= "    </tbody>\n";
    $message .= "</table>\n";
}
echo makeCard(12, $title, $message, false);

function makeEditCollection($finna, $artist, $license, $type, $id) {
    $checked = null;
    $data1 = array("getArtists" => 1, "active" => 1);
    $tmp_artist = callRest("POST", WEBROOT . "/rest/artists.php", $data1, true);
    $data2 = array("getLegals" => 1, "active" => 1);
    $tmp_license = callRest("POST", WEBROOT . "/rest/legals.php", $data2, true);
    $data3 = array("getLegalTypes" => 1, "active" => 1);
    $tmp_type = callRest("POST", WEBROOT . "/rest/legalTypes.php", $data3, true);
    if ($finna == 1) {
        $checked = "checked";
    }
    $msg = "<div class='form-check'>\n";
    $msg .= "   <input type='checkbox' name='finna' id='finna' value='1' $checked>\n";
    $msg .= "   <label class='form-check-label' for='inlineRadio1'>Digitointierän tiedostot julkaistaan Finnassa</label>\n";
    $msg .= "</div>\n";
    $msg .= makeDropDownFromArray($tmp_artist, "artist", "artist_id", false, 12, "Tekijä", false, "new_era", $artist);
    $msg .= makeDropDownFromArray($tmp_license, "legal", "legal_id", false, 12, "Oikeus", false, "new_era", $license);
    $msg .= makeDropDownFromArray($tmp_type, "type", "type_id", false, 12, "Oikeustyyppi", false, "new_era", $type);
    $msg .= "<input type='hidden' name='id' Value='$id'>";
    return $msg;
}
