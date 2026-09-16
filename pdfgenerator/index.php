<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../settings.php';
showErrors(0);
$mpdf = new \Mpdf\Mpdf();

$url = urldecode($_REQUEST['url']);

if (strpos($url, PDF_ACCESS_URL) === false) {
    die("Access denied");
}

// For $_POST i.e. forms with fields
if (count($_POST) > 0) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    foreach ($_POST as $name => $post) {
        $formvars = array($name => $post . " \n");
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $formvars);
    $html = curl_exec($ch);
    curl_close($ch);
} elseif (ini_get('allow_url_fopen')) {
    $html = file_get_contents($url);
} else {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $html = curl_exec($ch);
    curl_close($ch);
}

$mpdf = new \Mpdf\Mpdf();

$mpdf->useSubstitutions = true; // optional - just as an example
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');  // optional - just as an example
$mpdf->SetHeader('Page {PAGENO}');  // optional - just as an example
$mpdf->CSSselectMedia = 'mpdf'; // assuming you used this in the document header
$mpdf->setBasePath($url);
$mpdf->WriteHTML($html);

$mpdf->Output();
