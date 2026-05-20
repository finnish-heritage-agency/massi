<?php

require_once '../rest/rest_settings.php';
$db = getConnect();
$objectId = "1985396";
$museum = new Museum();
//$museum->setModuleId("1046887"); //ID 953159
$museum->setModuleId($objectId);

$ids = $museum->checkTrueThumbnailBoos();

//Näillä kaikilla on thumbnail arvo true
debug($ids);

//Ja jos löytyy jo siirretystä erästä, niin ei muuteta arvoa

foreach ($ids as $id) {
    if (sendedByMassaSoftware($id, $objectId) == 0) {
        $museum->setDisableId($id);
        $changed = $museum->changeThumbnailBooStatus();
        debug($changed);
    }
}

