<?php

/**
 * JREPO @ SOFTREPO 1.7.2020
 */
//https://github.com/picqer/php-barcode-generator
include(REST . "/oop/barCode/BarcodeGenerator.php");
include(REST . "/oop/barCode/BarcodeGeneratorPNG.php");
include(REST . "/oop/barCode/BarcodeGeneratorSVG.php");
include(REST . "/oop/barCode/BarcodeGeneratorJPG.php");
include(REST . "/oop/barCode/BarcodeGeneratorHTML.php");

class barCode {

    private $format; //barcode format (html,img, png,svg)
    private $codetype; //Type of BarCode
    private $text; //string/int to barcode

    public function __construct() {

    }

    function getFormat() {
        return strtoupper($this->format);
    }

    function getCodeType() {
        return $this->codetype;
    }

    function getText() {
        return $this->text;
    }

    function setFormat($format) {
        $this->format = $format;
    }

    function setCodeType($codetype) {
        $tmp = strtolower($codetype);
        if ($tmp == "svg" || $tmp == "png" || $tmp == "jpg" || $tmp == "html") {
            $this->codetype = $codetype;
        } else {
            $this->codetype = "";
        }
    }

    function setText($text) {
        $this->text = $text;
    }

    public function showHelp() {
        $msg = "<h3>Viivakoodin käyttö</h3>";
        $msg .= "<p>index.php?<strong>type</strong>=svg&<strong>text</strong>=fdsfdsfewplfewplfw&<strong>code</strong>=TYPE_CODE_128<br /><br />";
        $msg .= "Sovellus parsii GET metodeista seuraavat tiedot:<br />\n";
        $msg .= " - type: Millä kuvaformaatilla viivakoodi näytetään<br />\n";
        $msg .= " - text: Mitä textä viivakoodin sisällytetään. HUOM. Jotkut viivakoodiformaatit tulostavat vain numeerista tietoa.<br />\n";
        $msg .= " - code: Millä formaatilla viivakoodi tehdään<br /></p>\n";
        $msg .= "<ul><strong>TYYPIT</strong>";
        $msg .= "<li>svg</li>\n";
        $msg .= "<li>html</li>\n";
        $msg .= "<li>jpg</li>\n";
        $msg .= "<li>png</li>\n";
        $msg .= "</ul>\n";
        $msg .= "<ul><strong>FORMAATIT</strong>";
        $msg .= "<li>TYPE_CODE_39</li>\n";
        $msg .= "<li>TYPE_CODE_39_CHECKSUM</li>\n";
        $msg .= "<li>TYPE_CODE_39E</li>\n";
        $msg .= "<li>TYPE_CODE_39E_CHECKSUM</li>\n";
        $msg .= "<li>TYPE_STANDARD_2_5</li>\n";
        $msg .= "<li>TYPE_STANDARD_2_5_CHECKSUM</li>\n";
        $msg .= "<li>TYPE_INTERLEAVED_2_5</li>\n";
        $msg .= "<li>TYPE_INTERLEAVED_2_5_CHECKSUM</li>\n";
        $msg .= "<li>TYPE_CODE_128</li>\n";
        $msg .= "<li>TYPE_CODE_128_A</li>\n";
        $msg .= "<li>TYPE_CODE_128_B</li>\n";
        $msg .= "<li>TYPE_CODE_128_C</li>\n";
        $msg .= "<li>TYPE_EAN_2</li>\n";
        $msg .= "<li>TYPE_EAN_5</li>\n";
        $msg .= "<li>TYPE_EAN_8</li>\n";
        $msg .= "<li>TYPE_EAN_13</li>\n";
        $msg .= "<li>TYPE_UPC_A</li>\n";
        $msg .= "<li>TYPE_UPC_E</li>\n";
        $msg .= "<li>TYPE_MSI</li>\n";
        $msg .= "<li>TYPE_MSI_CHECKSUM</li>\n";
        $msg .= "<li>TYPE_POSTNET</li>\n";
        $msg .= "<li>TYPE_PLANET</li>\n";
        $msg .= "<li>TYPE_RMS4CC</li>\n";
        $msg .= "<li>TYPE_KIX</li>\n";
        $msg .= "<li>TYPE_IMB</li>\n";
        $msg .= "<li>TYPE_CODABAR</li>\n";
        $msg .= "<li>TYPE_CODE_11</li>\n";
        $msg .= "<li>TYPE_PHARMA_CODE</li>\n";
        $msg .= "<li>TYPE_PHARMA_CODE_TWO_TRACKS</li>\n";
        $msg .= "</ul>";

        return $msg;
    }

}
