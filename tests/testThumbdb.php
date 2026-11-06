<?php

require_once '../settings.php';
debug($poistettavat_tiedostot);
$tmp = array('..', '.');
$data = array_merge($tmp, $poistettavat_tiedostot);
$tmp_files = array_diff(scandir(PICTURE_FOLDER . "testi"), $data);
debug($data);
