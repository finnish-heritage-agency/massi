<?php

require_once '../rest/rest_settings.php';
$fileObjectId = 1742592;
$museum = new Museum();
$museum->login();
$message = $museum->deleteAttachment($fileObjectId);
debug($message);

