<?php

require_once '../rest/rest_settings.php';

$moduleId = 2164094;
$file = "/kuvat/HK6000_1555/HK6000_1555__11.jpg";

$museum = new Museum();
$museum->setModuleId($moduleId);

$museum->setFile($file);
$ok = $museum->sendFile();
debug($museum);
