<?php

require_once '../rest/rest_settings.php';

$museum = new Museum();
$museum->Search("HK6000:1554");
$message = $museum->getResponse(true);
debug($message);

