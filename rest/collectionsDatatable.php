<?php

//https://makitweb.com/datatable-ajax-pagination-with-php-and-pdo/

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}
require_once REST . 'rest_settings.php';
ini_set("display_errors", 0);
$message = null;
$draw = 0;
$row = 0;
$rowperpage = 0;
$columnIndex = 0;
$columnName = "";
$columnSortOrder = "asc";
$searchValue = "";
$db = getConnect();

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == "lista_id") {
    $columnName = "L.lista_id";
}

$searchQuery = " ";
if ($searchValue != '') {
    $searchQuery1 = " AND (otsikko LIKE :otsikko )";
    $searchQuery2 = " AND (L.otsikko LIKE :otsikko )";
    $searchArray = array(
        'otsikko' => "%$searchValue%",
    );
}

## Total number of records without filtering
$stmt = $db->prepare("SELECT COUNT(*) AS allcount FROM listat ");
$stmt->execute();
$records = $stmt->fetch();

$totalRecords = $records['allcount'];
## Total number of records with filtering
//$stmt = $db->prepare("SELECT COUNT(*) AS allcount FROM listat");
$stmt = $db->prepare("SELECT COUNT(*) AS allcount FROM listat WHERE 1 " . $searchQuery1);
$stmt->execute($searchArray);
$records = $stmt->fetch();
$totalRecordwithFilter = $records['allcount'];
## Fetch records
//$stmt = $db->prepare("SELECT * FROM listat WHERE 1 " . $searchQuery . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT 25,100 ");
$stmt = $db->prepare("SELECT L.*, COUNT(1) as maara, LR.*
                FROM listat L
                LEFT JOIN listan_rivit LR
                ON L.lista_id = LR.lista_id  WHERE 1 " . $searchQuery2 . "
                GROUP BY L.lista_id ORDER BY $columnName $columnSortOrder LIMIT :limit,:offset ");
// Bind values
foreach ($searchArray as $key => $search) {
    $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', (int) $row, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $rowperpage, PDO::PARAM_INT);

$stmt->execute();
$rivit = $stmt->fetchAll();
foreach ($rivit as $rivi) {
    if ($rivi['finna'] == 1) {
        $text = "Kyllä";
    } else {
        $text = "Ei";
    }
    $sorting = sortDate(date("d.m.Y H:i:s", strtotime($rivi['paivays'])));
    $message = " (" . $sorting["week_day"] . ") " . $sorting["day"];
    $data[] = array(
        "lista_id" => $rivi['lista_id'],
        "otsikko" => $rivi['otsikko'],
        //"paivays" => date("d.m.Y H:i:s", strtotime($rivi['paivays'])),
        "paivays" => $message,
        "finna" => $text,
        "maara" => getCompletedRows($rivi["lista_id"]) . "/" . $rivi['maara'],
        "valmis" => isReady($rivi["lista_id"]), //Tässä kestää :/
    );
}


$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
