<?php

$message = submitPopup();
if (isset($_GET["more"])) {
    if ($_GET["more"] == "end_break") {
        unlink(SOFTWARE_BREAK);
        if (file_exists(SOFTWARE_BREAK)) {
            $_SESSION["tallennusvirhe"] = "Taukotiedostoa ei saatu poistettua.";
        } else {
            $ok = callRest("POST", WEBROOT . "/rest/checkMplusAttempts.php", array("reset" => 1), true);
            $_SESSION["tallennus_ok"] = "Sovelluksen tauko on päättynyt.<br />Uudelleen lähetetään listoja $ok kpl.";
        }
    } elseif ($_GET["more"] == "start_break") {
        touch(SOFTWARE_BREAK);
        $_SESSION["tallennusvirhe"] = "Sovelluksen tauko on aloitettu " . date("H:i:s", time());
    }
    header("location: ./");
}

$message .= "<p>Lataa CapturePro asetukset <a href='" . WEBROOT . "/job_setup_v01.zip'>Tästä.</a><br />";
$message .= "Sovellus on päivitetty " . UPDATED . "</p>";
$message .= "<table class='table table-bordered' width='100%'>\n";
$message .= "   <thead><tr><th>Määritys</th><th>Arvo</th><th>Selite</th></tr></thead>";
$message .= "    <tbody>\n";
$message .= makeTableRow("Omistaja", PREFIX);
$message .= makeTableRow("Tiedostojen lähetys M+ ", SEND_TIME, "Aika milloin tiedostoja lähetetään M+ järjestelmään");
$message .= makeTableRow("Uudelleenlähetys", RE_TRIES, "Kuinka monta kertaa yritetään lähettää tietoja uudestaan M+ järjestelmään");
$message .= makeTableRow("Digitointierän pituus (DATA_LENGTH)", DATA_LENGTH, "Kuinka monta merkkiä voidaan syöttää kopioi ja liitä toiminnolla");
$message .= makeTableRow("Syötteen katkasijat", showParsers(), "määritykset mitkä käyvät ns. syötettävän tekstin pilkkojana");
$message .= makeTableRow("Domain (MUSEUM_DOMAIN)", MUSEUM_DOMAIN, "M+ järjestelmän ns. käyttäjä");
$message .= makeTableRow("Sallittu sivusto (PDF_ACCESS_URL)", PDF_ACCESS_URL, "Vain tämän URL:n sivustoja voidaan tulostaa");
$message .= makeTableRow("Sovelluksen osoite (ROOT)", ROOT, "Sovelluksen juuriosoite");
$message .= makeTableRow("Sovelluksen URL (WEBROOT)", WEBROOT, "Sovelluksen URL-osoite");
$message .= makeTableRow("REST API-avain (APIKEY)", APIKEY, "REST rajapintaa varten");
$message .= makeTableRow("Exiftool (EXIFTOOL)", EXIFTOOL, "Työkalun staattinen sijainti");
//$message .= makeTableRow("Sovelluksen osoite ", FPDF_FONTPATH, "Sovelluksen juuriosoite");
$message .= makeTableRow("Lokien hakemisto", LOGS, "Sovelluksen lokien sijainti");
$message .= makeTableRow("Lokien ja onnistuneesti siirrettyjen aineistojen säilyvyys", SAVE_LOGS . " päivää", "Kuinka kauan sovelluksen lokeja säilytetään. Ja kuinka vanhat onnistuneet kuvat siirretään poistuvat hakemistoon.");
$message .= makeTableRow("Kuvien hakemisto", PICTURE_FOLDER, "Hakemisto, jota sovellus tarkkailee");
$message .= makeTableRow("Tiedostotyypit (TYPES)", nl2br(print_r(TYPES, true)));
$message .= makeTableRow("Finnaan vietävät tiedostomuodot", ".tif, .jpeg", "Kaikkia digitointierässä olevia tiedostomuotoja (esim. .dng) ei viedä Finnaan");
$message .= makeTableRow("M+ järjestelmä", M_URL, "Mihin osoitteeseen otetaan M+ yhteys");
$message .= makeTableRow("M+ järjestelmä", M_USERNAME, "M+ käyttäjätunnus");
$message .= makeTableRow("M+ järjestelmä", CACHE_LIFETIME . " sec", "Session voimassaoloaika");
$message .= makeTableRow("Siirtolokit", SHOW_SUCCESS_LOGS, "Näytetäänkö onnistuneet lokit edustapalvelussa");
$message .= makeTableRow("Sovelluksen katkotiedosto", SOFTWARE_BREAK, "Kun tiedosto on olemassa, sovellus lakkaa siirtojen uudelleenyrittämisen ja mitään taustaprosessia ei enää ajeta");
$message .= makeTableRow("Uudelleen yritysten määrä", MAX_ATTEMPTS, "Kuinka monta kertaa yritetään automaattisesti, ennen kuin laitetaan koko järjestelmä katkolle");

$message .= "    </tbody>\n";
$message .= "</table>\n";
$message .= "<a href='" . WEBROOT . "/sivu/asetukset/' class='btn btn-info text-right'>Lisää asetuksia</a>";
if (file_exists(SOFTWARE_BREAK)) {
    $message .= "<a href='./end_break' class='btn btn-success'>Päätä sovelluksen tauko</a>";
} else {
    $message .= "<a href='./start_break' class='btn btn-danger'>Sovellus tauolle</a>";
}
echo makeCard(12, text("techinal settings"), $message, false);

function makeTableRow($title, $value, $text = null) {
    $message = "       <tr>\n";
    $message .= "           <td>$title</td>\n";
    $message .= "           <td>$value</td>\n";
    $message .= "           <td>$text</td>\n";
    $message .= "       </tr>\n";
    return $message;
}
