<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/');
}

require_once REST . 'rest_settings.php';

ini_set('display_errors', 0);

$db = getConnect();

$draw = (int)($_POST['draw'] ?? 0);
$row = (int)($_POST['start'] ?? 0);
$rowperpage = (int)($_POST['length'] ?? 10);

$columnIndex = (int)($_POST['order'][0]['column'] ?? 0);
$columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
$searchValue = $_POST['search']['value'] ?? '';

$searchQuery1 = "";
$searchQuery2 = "";
$searchArray = [];
$data = [];

$columnName = $_POST['columns'][$columnIndex]['data'] ?? 'lista_id';

$allowedColumns = [
    'lista_id',
    'otsikko',
    'paivays',
    'finna',
    'maara',
    'valmis'
];

if (!in_array($columnName, $allowedColumns, true)) {
    $columnName = 'lista_id';
}

if ($columnName === 'lista_id') {
    $columnName = 'L.lista_id';
}

if (!in_array(strtolower($columnSortOrder), ['asc', 'desc'], true)) {
    $columnSortOrder = 'asc';
}

if ($searchValue !== '') {
    $searchQuery1 = " AND (otsikko LIKE :otsikko)";
    $searchQuery2 = " AND (L.otsikko LIKE :otsikko)";

    $searchArray = [
        'otsikko' => "%{$searchValue}%"
    ];
}

$stmt = $db->prepare("
    SELECT COUNT(*) AS allcount
    FROM listat
");
$stmt->execute();

$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $records['allcount'];

$stmt = $db->prepare("
    SELECT COUNT(*) AS allcount
    FROM listat
    WHERE 1 {$searchQuery1}
");

$stmt->execute($searchArray);

$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecordwithFilter = $records['allcount'];

$sql = "
SELECT
    L.*,
    COUNT(LR.rivi_id) AS maara
FROM listat L
LEFT JOIN listan_rivit LR
    ON L.lista_id = LR.lista_id
WHERE 1 {$searchQuery2}
GROUP BY L.lista_id
ORDER BY {$columnName} {$columnSortOrder}
LIMIT {$row}, {$rowperpage}
";

$stmt = $db->prepare($sql);

foreach ($searchArray as $key => $search) {
    $stmt->bindValue(":{$key}", $search, PDO::PARAM_STR);
}

$stmt->execute();

$rivit = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rivit as $rivi) {

    $text = ($rivi['finna'] == 1) ? "Kyllä" : "Ei";

    $sorting = sortDate(
        date("d.m.Y H:i:s", strtotime($rivi['paivays']))
    );

    $message = " (" . $sorting["week_day"] . ") " . $sorting["day"];

    $data[] = [
        "lista_id" => $rivi['lista_id'],
        "otsikko" => $rivi['otsikko'],
        "paivays" => $message,
        "finna" => $text,
        "maara" => getCompletedRows($rivi["lista_id"]) . "/" . $rivi['maara'],
        "valmis" => isReady($rivi["lista_id"]),
    ];
}

$response = [
    "draw" => $draw,
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
];

echo json_encode($response);
