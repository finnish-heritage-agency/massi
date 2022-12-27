<script>
    function openCollection(collection_id) {
        window.open("<?php echo WEBROOT ?>/sivu/era/" + collection_id + "/", "_self");
    }
</script>
<?php
//Tämä latautuu liian hitaasti. Tehty rest-Jquerylla uusi 10.5.2022
$message = submitPopup();
if (isset($_POST["add_collection"])) {
    if ($_POST["rows"] != "") {
        if (strlen($_POST["rows"]) > DATA_LENGTH) {
            $rows = substr($_POST["rows"], 0, DATA_LENGTH);
        } else {
            $rows = checkPost($_POST["rows"]);
        }
        if (!isset($_POST["finna"]) || $_POST["finna"] == 0) {
            $finna = 0;
        } else {
            $finna = 1;
        }

        $data = array(
            "rows" => $rows,
            "batch_saver" => checkPost($_POST["batch_saver"]),
            "title" => checkPost($_POST["title"]),
            "legal_id" => checkPost($_POST["legal_id"]),
            "type_id" => checkPost($_POST["type_id"]),
            "artist_id" => checkPost($_POST["artist_id"]),
            "finna" => $finna,
        );

        $tmp2 = callRest("POST", WEBROOT . "/rest/addNewCollection.php", $data);
        if (strpos($tmp2, "Tallennettu") !== false) {
            $_SESSION["tallennus_ok"] = json_decode($tmp2);
        } else {
            $_SESSION["tallennusvirhe"] = json_decode($tmp2);
        }
    } else {
        $_SESSION["tallennusvirhe"] = json_decode("Ei tallennettu yhtään riviä");
    }
    header("location: " . WEBROOT . "/sivu/erat/");
} elseif (isset($_GET["merkitse_valmiiksi"])) {
    if (is_numeric($_GET["valmis"])) {
        $lista_id = $_GET["valmis"];
    }
    //sleep(2); //Jotta sweet alert tallennettu näkyisi hetken...

    $data = array("markAsReady" => 1, "row_id" => $lista_id);
    $tmp = callRest("POST", WEBROOT . "/rest/collections.php", $data, true);

    if ($tmp > 0) {
        $data1 = array(
            "newJobsbyListaId" => 1,
            "lista_id" => $lista_id,
        );
        $tmp2 = callRest("POST", WEBROOT . "/rest/addNewJob.php", $data1, true);
        $message = "Digitointierä muutettu valmiiksi. Objekteja erässä: $tmp kpl.";
        if ($tmp2 != 1) {
            $message .= "<br />Uusia töitä tallennettiin: $tmp2 kpl.";
        }
        $_SESSION["tallennus_ok"] = $message;
    } else {
        $_SESSION["tallennusvirhe"] = "Digitointierää ei saatu muutettua valmiiksi. VIRHE: $tmp";
    }
    if (isset($_GET["collection_site"]) && $_GET["collection_site"] == "true") {
        header("location: " . WEBROOT . "/sivu/era/$lista_id/");
    } else {
        header("location: " . WEBROOT . "/sivu/erat/");
    }
}
$data = array(
    "getCollections" => 1,
    "limit" => 1000,
);
//echo lapTime() . " ";
$tmp = callRest("POST", WEBROOT . "/rest/collections.php", $data, true);
//echo lapTime() . " ";
$message .= "<table class='table table-bordered' id='dataTable' width='100%'>\n";
$message .= "   <thead><tr><th>#</th><th>Päiväys<br />Erän tallentaja</th><th>Digitointierä</th><th>Finna</th>";
if (BY_DIGITOINTIERA == true) {
    $message .= "<th class='text-center '>Valmis</th>";
} else {
    $message .= "<th class='text-center '>Valmiina</th>";
}
$message .= "</tr></thead>\n";
$message .= "    <tbody>\n";
$olds = strtotime(OLD_COLLECTIONS);
foreach ($tmp as $row) {
    $class = null;
    $lisays = "";
    $open_page = "onclick='openCollection(" . $row->lista_id . ")' ";
    $row_color = "onmouseover='ChangeColor(this, true);' onmouseout='ChangeColor(this, false);'";
    $date = strtotime($row->paivays);
    if ($olds >= $date) {
        $class = "bg-gradient-light ";
        $last_text = " Vanha digitointierä.";
        $lisays = " HUOM. VANHA";
    } elseif ($row->rivi_valmis == 2) {
        $class = "bg-gradient-success text-gray-100";
        $last_text = "<i class='fas fa-check'></i> Digitointierä on valmis.";
    } elseif ($row->rivi_valmis == 1) {//Keskeneräinen. Taustajärjestelmä käsittelee parhaillaan
        $class = "bg-gradient-warning text-gray-900";
        $last_text = "<i class='fas fa-hourglass-half'></i> Digitointierässä kesken: " . $row->kesken . " kpl rivejä.";
    } elseif ($row->rivi_valmis == -1) {//Keskeneräinen. Taustajärjestelmä käsittelee parhaillaan
        $class = "bg-gradient-danger text-gray-900";
        //$last_text = "<i class='fas fa-angry'></i> Ongelmia: " . $row->epaonnistuneet . " kpl rivejä.";
        $last_text = "<i class='fas fa-exclamation'></i> Ongelmia: " . $row->epaonnistuneet . " kpl rivejä.";
    } else {
        $last_text = "<button class='btn btn-sm btn-outline-success' aria-label='' onclick=\"varmistus(" . $row->lista_id . ")\">Aloita tiedostojen siirto</button>";
    }

    $message .= "       <tr class='$class' $row_color>\n";
    $message .= "<td class='pointer' $open_page>" . $row->lista_id . "</td> ";
    $sorting = sortDate(date("d.m.Y H:i:s", strtotime($row->paivays)));
    $message .= "<td class='pointer' $open_page>" . $sorting["sort"];
    $message .= " (" . $sorting["week_day"] . ") " . $sorting["day"];
    if ($row->eran_tallentaja != "") {
        $message .= "<br /><i class='fas fa-smile'></i> " . $row->eran_tallentaja;
    }
    $message .= "</td>\n";
    $message .= "           <td class='pointer' $open_page>" . strip_tags($row->otsikko) . "$lisays</td>\n";
    $message .= "           <td class='pointer' $open_page>" . changeReturn($row->finna) . "</td>\n";
    if (BY_DIGITOINTIERA == true) {
        $message .= "           <td class='text-center pointer with_button'>$last_text <br />Viety: " . $row->valmiina . "/" . $row->maara;
        $message .= "</td>\n";
    } else {
        $message .= "           <td class='text-center pointer' $open_page>Viety: " . $row->valmiina . "/" . $row->maara . "</td>\n";
    }
    $message .= "       </tr>\n";
}
$message .= "    </tbody>\n";
$message .= "</table>\n";

echo makeCard(12, text("digitization batchs", 1), $message, false);
echo "<script>
    function ChangeColor(tableRow, highLight) {
        if (highLight) {
            tableRow.style.backgroundColor = '#385ece';
            tableRow.style.color = 'white';
            tableRow.style.fontWeight = '900';
        } else {
            tableRow.style.backgroundColor = 'white';
            tableRow.style.color = '#858796';
            tableRow.style.fontWeight = '400';
        }
    }
</script>";
