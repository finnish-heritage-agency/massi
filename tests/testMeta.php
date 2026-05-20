<?php

if (!defined('ROOT')) {
    define('ROOT', __DIR__ . '/../');
}

//require_once '../rest/rest_settings.php';
require_once '../settings.php';

$file_object_id = 2172485;
$file = "/kuvat/VKKSLS8000_28_1A/VKKSLS8000_28_1A.JPG";

$collection = new SendCollection("VKKSLS8000_28_1A", true, MUSEUM_DOMAIN, 15);
$collection->setModule("multimedia"); //Lähetetään tiedot multimedia objektiin
$collection->makeDefinitions();
$files = $collection->getFiles();
debug($collection->getError());

