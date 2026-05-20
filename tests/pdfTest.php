<?php

$data = array(
    "type" => "jpg",
    "text" => "fsdofidsofio34 fsdo",
    "code" => "TYPE_CODE_128",
);
require_once '../settings.php';

$barCode = callRest("POST", WEBROOT . "/rest/barCodeGenerator.php", $data);
$local_name = "/tmp/" . time() . '.jpg';
file_put_contents($local_name, $barCode); //create image locall

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 5, "testing");
//vasen, ylhäältä, pituus, leveys
$pdf->Image($local_name, 40, 10, 50, 5, "JPG"); //add image to pdf
unlink($local_name); //delete local image
$pdf->Output();
