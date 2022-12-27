<?php

/**
 * Selaimella
 */
require_once '../rest/rest_settings.php';

$museum = new Museum();
$museum->setModuleId(1261232);
//$museum->setModuleName("Object");
$rajapinnalta = array("sendMPlus" => "PD94bWwgdmVyc2lvbj0nMS4wJyBlbmNvZGluZz0nVVRGLTgnPz4KPGFwcGxpY2F0aW9uIHhtbG5zPSdodHRwOi8vd3d3LnpldGNvbS5jb20vcmlhL3dzL21vZHVsZSc+CiAgIDxtb2R1bGVzPgogICAgICAgPG1vZHVsZSBuYW1lPSdtdWx0aW1lZGlhJz4KICAgICAgICAgICA8bW9kdWxlSXRlbT4KPHJlcGVhdGFibGVHcm91cCBuYW1lPSdNdWxQdWJsU3RhdHVzTkJBMDFHcnAnPgogICA8cmVwZWF0YWJsZUdyb3VwSXRlbT4KICAgICAgIDx2b2NhYnVsYXJ5UmVmZXJlbmNlIG5hbWU9J1N0YXR1c1ZvYyc+CiAgICAgICAgICAgPHZvY2FidWxhcnlSZWZlcmVuY2VJdGVtIGlkPScxMTg1NzUnLz4KICAgICAgIDwvdm9jYWJ1bGFyeVJlZmVyZW5jZT4KICAgICAgIDx2b2NhYnVsYXJ5UmVmZXJlbmNlIG5hbWU9J1R5cGVWb2MnPgogICAgICAgICAgIDx2b2NhYnVsYXJ5UmVmZXJlbmNlSXRlbSBpZD0nMzIxNzE1Jy8+CiAgICAgICA8L3ZvY2FidWxhcnlSZWZlcmVuY2U+CiAgIDwvcmVwZWF0YWJsZUdyb3VwSXRlbT4KPC9yZXBlYXRhYmxlR3JvdXA+Cjxtb2R1bGVSZWZlcmVuY2UgbmFtZT0nTXVsUGhvdG9ncmFwaGVyUmVmJyB0YXJnZXRNb2R1bGU9J0FkZHJlc3MnPgogICA8bW9kdWxlUmVmZXJlbmNlSXRlbSBtb2R1bGVJdGVtSWQ9JzExODIwMCc+CiAgIDwvbW9kdWxlUmVmZXJlbmNlSXRlbT4KPC9tb2R1bGVSZWZlcmVuY2U+Cjxjb21wb3NpdGUgbmFtZT0nTXVsUmVmZXJlbmNlc0NyZSc+CiAgIDxjb21wb3NpdGVJdGVtPgogICAgICAgPG1vZHVsZVJlZmVyZW5jZSBuYW1lPSdNdWxPYmplY3RSZWYnIHRhcmdldE1vZHVsZT0nT2JqZWN0Jz4KICAgICAgICAgICA8bW9kdWxlUmVmZXJlbmNlSXRlbSBtb2R1bGVJdGVtSWQ9JzEyNjEyMzInPgo8ZGF0YUZpZWxkIG5hbWU9J1RodW1ibmFpbEJvbyc+Cjx2YWx1ZT50cnVlPC92YWx1ZT4KPGZvcm1hdHRlZFZhbHVlIGxhbmd1YWdlPSdlbic+eWVzPC9mb3JtYXR0ZWRWYWx1ZT4KPC9kYXRhRmllbGQ+CiAgICAgICAgICAgPC9tb2R1bGVSZWZlcmVuY2VJdGVtPgogICAgICAgPC9tb2R1bGVSZWZlcmVuY2U+CiAgIDwvY29tcG9zaXRlSXRlbT4KPC9jb21wb3NpdGU+CiAgICAgICAgICAgPC9tb2R1bGVJdGVtPgogICAgICAgPC9tb2R1bGU+CiAgIDwvbW9kdWxlcz4KPC9hcHBsaWNhdGlvbj4K",
    "moduleId" => 1261232);
$xml = base64_decode($rajapinnalta["sendMPlus"]);

$museum->setParameter($xml);
$museum->setType("definitions");
$museum->setEndPoint("module/Multimedia/");
$ok = $museum->fileDefinitions($xml);

/*
  // Tiedoston lähetys toimii!!! 17.11.2020
  $museum->setModuleId(1261232);
  $museum->setModuleName("Object");
  $museum->setFile("/kuvat/HK6000_1553/HK6000_1553__1.tif");
  $ok = $museum->sendFile();

  if ($ok == 1) {
  echo "siirto onnistui!";
  }
 *
 */
debug($ok);

/*
  $museum->setModuleId(1261232);
  $museum->sendFinnaRequest();

  $ok = $museum->getResponse();
  debug($ok);
 */

/* Tämä toimii 17.11.2020
  $museum->Search("HK6000:1553");
  debug($museum);
 *
 */
die();
/*

  //$ok = $museum->makeCurl("module/virtualField/ObjObjectNumberVrt/HK8206:9");
  //$ok = $museum->makeCurl("module/Object/1951762");
  //$ok = $museum->makeCurl("module/Object/1951762");
  debug($museum);
  if ($museum->getLoginStatus() < 0) {
  echo "Cant log in... ERROR code: " . $museum->getLoginStatus();
  die();
  }
  debug($museum->getResponse(true));
 *
 */

//"https://museoliitto2.vserver.fi/MpWeb-mpMuseoNba/ria-ws/application/module/Object/1261232" haetaan yhden objektin tietueet
/*
curl -X "GET" "https://museoliitto2.vserver.fi/MpWeb-mpMuseoNba/ria-ws/application/module/Object/1720419"  -u MV_jounrepo:
curl -X "GET" "https://museoliitto2.vserver.fi/MpWeb-mpMuseoNba/ria-ws/application/vocabulary/instances/ObjPublStatusStatusNBA01Vgr/nodes/search?limit=10"  -u MV_jounrepo:
curl -X "GET" "https://museoliitto2.vserver.fi/MpWeb-mpMuseoNba/ria-ws/application/vocabulary/instances/ObjPublStatusTypeNBA01Vgr/nodes/search?limit=10"  -u MV_jounrepo:
curl -X "GET" "https://museoliitto2.vserver.fi/MpWeb-mpMuseoNba/ria-ws/application/vocabulary/instances/ObjPublStatusNBA01Grp/nodes/search?limit=10"  -u MV_jounrepo:
curl -X "GET" "https://museoliitto2.vserver.fi/MpWeb-mpMuseoNba/ria-ws/application/vocabulary/instances/ObjPublStatusStatusNBA01Vgr/nodes/search?limit=10"  -u MV_jounrepo:
curl -X "GET" "https://museoliitto2.vserver.fi/MpWeb-mpMuseoNba/ria-ws/application/module/Object/1261232"  -u MV_jounrepo:
 *
 */
