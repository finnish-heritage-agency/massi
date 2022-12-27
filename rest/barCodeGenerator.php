<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}

require_once REST . 'oop/barCode.php';
require_once REST . 'rest_settings.php';
$barCode = new barCode();

$format = "";
$text = "";
$type = "";
if (isset($_GET["type"])) { //barcode format (html,img, png,svg)
    $type = checkPost($_GET["type"]);
}
if (isset($_GET["text"])) { //string/int to barcode
    $text = checkPost($_GET["text"]);
    $text = str_replace(":", "_", $text);
}

if (isset($_GET["code"])) { //Type of BarCode
    $format = checkPost($_GET["code"]);
}
if (isset($_POST["type"])) { //barcode format (html,img, png,svg)
    $type = checkPost($_POST["type"]);
}

if (isset($_POST["text"])) { //string/int to barcode
    $text = checkPost($_POST["text"]);
    $text = str_replace(":", "_", $text);
}
if (isset($_POST["code"])) { //Type of BarCode
    $format = checkPost($_POST["code"]);
}

if ($format != "" && $text != "" && $type != "") {
    $barCode->setFormat($format);
    $barCode->setText($text);
    $barCode->setCodeType($type);
} else {
    echo $barCode->showHelp();
    die();
}

if ($barCode->getCodeType() == "") {
    die();
}
/*
  if (strlen($text) < 16) {
  $text = "min length is 16 chars ";
  $barCode->setText($text);
  echo $text;
  }
 *
 */

/*
  if ($barCode->getCodeType() == "png") {
  $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
  } else */

if ($barCode->getCodeType() == "svg") {
    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
    //Jotta PDF tulostukseen tulee myös viivakoodit, käytetään svg tyyliä, ilman object wrapperia
//    $svg = base64_encode($generator->getBarcode($barCode->getText(), $barCode->getFormat()));
//    echo "<object type='image/svg+xml' data='$svg'><img src='data:image/svg+xml;base64,$svg' /></object>";
//          <img src='data:image/svg+xml;base64,$svg' />
    echo '<img src="data:image/svg+xml;base64,' . base64_encode($generator->getBarcode($barCode->getText(), $barCode->getFormat())) . '" />';
} elseif ($barCode->getCodeType() == "jpg") {
    $generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
    header('Last-Modified: ' . date('r'));
    header('Accept-Ranges: bytes');
    header('Content-Type: image/jpeg');
    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($barCode->getText(), $barCode->getFormat())) . '" />';
    $viesti = $generator->getBarcode($barCode->getText(), $barCode->getFormat());
//    $img = imagecreatefromstring($viesti);
//    imagejpeg($img);
//    imagedestroy($img);
} elseif ($barCode->getCodeType() == "html") {
    $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
    print($generator->getBarcode($barCode->getText(), $barCode->getFormat()));
}
/* examples
 *
$generatorSVG = new Picqer\Barcode\BarcodeGeneratorSVG();
file_put_contents('tests/verified-files/081231723897-ean13.svg', $generatorSVG->getBarcode('081231723897', $generatorSVG::TYPE_EAN_13));

$generatorHTML = new Picqer\Barcode\BarcodeGeneratorHTML();
file_put_contents('tests/verified-files/081231723897-code128.html', $generatorHTML->getBarcode('081231723897', $generatorHTML::TYPE_CODE_128));

$generatorSVG = new Picqer\Barcode\BarcodeGeneratorSVG();
file_put_contents('tests/verified-files/0049000004632-ean13.svg', $generatorSVG->getBarcode('0049000004632', $generatorSVG::TYPE_EAN_13));
 */