<?php

/**
 * Haetaan kokoelman / kokoelmien tiedot
 */
if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
$message = null;
$db = getConnect();
$collection = new Collection($db);
if (isset($_POST["limit"]) && is_numeric($_POST["limit"])) {
    $limit = checkNumber($_POST["limit"]);
} else {
    $limit = 10;
}
if (isset($_POST["getCollections"])) {
    $message = $collection->allCollections($limit);
} elseif (isset($_POST["getOneCollectionById"])) {
    if (isset($_POST["getOneCollectionById"]) && is_numeric($_POST["getOneCollectionById"])) {
        $collection_id = checkNumber($_POST["getOneCollectionById"]);
    }
    $message = $collection->oneCollection($collection_id);
    $message1 = $collection->CollectionRows($collection_id);
    array_push($message, $message1);
} elseif (isset($_POST["isCollectionReady"])) {
    $collection_name = checkPost($_POST["isCollectionReady"]);
    $message = $collection->checkCollection($collection_name);
} elseif (isset($_POST["getCollectionDataToXML"])) {
    if (isset($_POST["name"])) {
        $collection_name = checkPost($_POST["name"]);
    }
    if (!$collection_name) {
        jsonError("Required arguments missing");
        return;
    }
    $message = $collection->getCollectionDataToXML($collection_name);
    //Return also right (m_oikeus) and right type (m_tyyppi)
} elseif (isset($_POST["markAsReady"])) {
    if (isset($_POST["row_id"])) {
        $row_id = checkNumber($_POST["row_id"]);
    }
    if (!$row_id) {
        jsonError("Required arguments missing");
        return;
    }
    $message = $collection->markBatchToReady($row_id);
} elseif (isset($_POST["getSavedFileData"])) {
    if (isset($_POST["filename"])) { //basename
        $filename = $_POST["filename"];
    }
    if (!$filename) {
        jsonError("Required arguments missing");
        return;
    }
    $message = $collection->getfiledata($filename);
} elseif (isset($_POST["readyToRemove"])) {
    $message = $collection->readyToRemove();
} elseif (isset($_POST["checkCollectionsRows"])) {
    if (isset($_POST["rows"])) {
        $rows = $_POST["rows"];
        $rows = explode("  ", $rows);
    }
    if (!$rows) {
        jsonError("Required arguments missing $rows");
        return;
    }
    $message = $collection->checkNewRows($rows);
} elseif (isset($_POST["editCollectionValues"])) {
    if (isset($_POST["finna"]) && $_POST["finna"] != "") {
        $finna = $_POST["finna"];
    } else {
        $finna = 0;
    }
    if (isset($_POST["artist_id"]) && $_POST["artist_id"] != "") {
        $artist_id = $_POST["artist_id"];
    } else {
        $artist_id = 0;
    }
    if (isset($_POST["license_id"]) && $_POST["license_id"] != "") {
        $license_id = $_POST["license_id"];
    } else {
        $license_id = 0;
    }
    if (isset($_POST["type_id"]) && $_POST["type_id"] != "") {
        $type_id = $_POST["type_id"];
    } else {
        $type_id = 0;
    }
    $id = $_POST["id"];

    $collection->setFinna($finna);
    $collection->setArtistId($artist_id);
    $collection->setLegalTypeId($type_id);
    $collection->setLicenseId($license_id);
    $collection->setId($id);
    $message = $collection->editCollectionValues();
} elseif (isset($_POST["saveCountOfTheTries"])) {
    if (isset($_POST["row_id"])) {
        $row_id = checkPost($_POST["row_id"]);
        $collection->setId($row_id);
        $message = $collection->saveCountOfTheTries();
    } elseif (isset($_POST["kokoelmatunnus"])) {
        $kokoelmatunnus = checkPost($_POST["kokoelmatunnus"]);
        $collection->setCollectionTitle($kokoelmatunnus);
        $message = $collection->saveCountOfTheTries(true);
    } else {
        jsonError("Required arguments missing");
        return;
    }

    //Alwasys adds +1
}

echo(json_encode($message));

