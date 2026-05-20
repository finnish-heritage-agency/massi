<?php

/**
 * Lets search object id by object name from M+ service
 */
require_once '../rest/rest_settings.php';
$tried = 0;
$museum = new Museum();
if (isset($_POST["search"])) {
    $search = checkPost($_POST["search"]);
}
if (isset($_POST["re_tries"]) && is_numeric($_POST["re_tries"])) {
    $re_tries = $_POST["re_tries"];
} else {
    $re_tries = 1;
}
while ($re_tries > $tried) {
    $museum->Search($search);
    $id = $museum->getResponse(true);
    if ($id >= 1) {
        $tried = 100; //Just bigger than re_tries
    }
    $tried++;
}
if ($museum->getLoginStatus() < 0) {
    $message = "Cant log in... ERROR code: " . $museum->getLoginStatus() . "TEXT: " . $museum->getLoginText();
} else {
    $message = $id;
}
echo(json_encode($message));
