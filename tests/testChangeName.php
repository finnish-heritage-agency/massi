<?php

require_once '../rest/rest_settings.php';
$moduleId = "1688186";
$file = "HK6000_1556.tif";
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<application xmlns=\"http://www.zetcom.com/ria/ws/module\">\n";
$xml .= "  <modules>\n";
$xml .= "    <module name=\"Multimedia\">\n";
$xml .= "      <moduleItem id='$moduleId'>\n";
$xml .= "        <dataField dataType='Varchar' name='MulOriginalFileTxt'>\n";
$xml .= "           <value>$file</value>\n";
$xml .= "        </dataField>\n";
$xml .= "      </moduleItem>\n";
$xml .= "    </module>\n";
$xml .= "  </modules>\n";
$xml .= "</application>";
//$tmp_data = array("changeName" => base64_encode($xml), "moduleId" => $file_id);

$museum = new Museum();
$museum->setFile($file);
$museum->setModuleId($moduleId);
$ok = $museum->changeFilename($xml);
debug($ok);

