<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../settings.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$cookie = session_name() . '=' . session_id();

/*
 * Vapauta session lukko ennen cURL-kutsua,
 * jotta print-sivu voi käyttää samaa sessiota.
 */
session_write_close();

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

    curl_setopt(
        $ch,
        CURLOPT_COOKIE,
        $cookie
    );

    foreach ($_POST as $name => $post) {
        $formvars = array($name => $post . " \n");
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $formvars);

    $html = curl_exec($ch);

    curl_close($ch);

} elseif (false) {

    $html = file_get_contents($url);

} else {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt(
        $ch,
        CURLOPT_COOKIE,
        $cookie
    );

    $html = curl_exec($ch);

    curl_close($ch);
}

$mpdf = new \Mpdf\Mpdf();

$mpdf->useSubstitutions = true;
$mpdf->SetHeader('Page {PAGENO}');
$mpdf->CSSselectMedia = 'mpdf';
$mpdf->setBasePath($url);
$mpdf->WriteHTML($html);

$mpdf->Output();