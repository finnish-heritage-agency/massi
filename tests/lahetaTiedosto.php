<?php

if (!defined('REST')) {
    define('REST', dirname(__FILE__) . '/../rest/');
}
set_time_limit(2);
require_once REST . 'rest_settings.php';
$array["sendMPlus"] = "PGFwcGxpY2F0aW9uIHhtbG5zPSdodHRwOi8vd3d3LnpldGNvbS5jb20vcmlhL3dzL21vZHVsZSc+CiAgIDxtb2R1bGVzPgogICAgICAgPG1vZHVsZSBuYW1lPSdtdWx0aW1lZGlhJz4KICAgICAgICAgICA8bW9kdWxlSXRlbT4KPHJlcGVhdGFibGVHcm91cCBuYW1lPSdNdWxSaWdodHNHcnAnIHNpemU9JzEnPgogICA8cmVwZWF0YWJsZUdyb3VwSXRlbT4KICAgICAgIDx2b2NhYnVsYXJ5UmVmZXJlbmNlIG5hbWU9J1R5cGVWb2MnIGlkPSczMzYyMScgaW5zdGFuY2VOYW1lPSdNdWxSaWdodHNUeXBlVmdyJz4KICAgICAgICAgICA8dm9jYWJ1bGFyeVJlZmVyZW5jZUl0ZW0gaWQ9JzMyMTI1Myc+CiAgICAgICAgICAgPC92b2NhYnVsYXJ5UmVmZXJlbmNlSXRlbT4KICAgICAgIDwvdm9jYWJ1bGFyeVJlZmVyZW5jZT4KICAgICAgIDx2b2NhYnVsYXJ5UmVmZXJlbmNlIG5hbWU9J1JpZ2h0TkJBMDFWb2MnIGlkPSc2MDYxOCcgaW5zdGFuY2VOYW1lPSdNdWxSaWdodHNSaWdodE5CQTAxVmdyJz4KICAgICAgICAgICA8dm9jYWJ1bGFyeVJlZmVyZW5jZUl0ZW0gaWQ9JzI3NzQ3NSc+CiAgICAgICAgICAgPC92b2NhYnVsYXJ5UmVmZXJlbmNlSXRlbT4KICAgICAgIDwvdm9jYWJ1bGFyeVJlZmVyZW5jZT4KICAgPC9yZXBlYXRhYmxlR3JvdXBJdGVtPgo8L3JlcGVhdGFibGVHcm91cD4KCjxtb2R1bGVSZWZlcmVuY2UgbmFtZT0nTXVsUGhvdG9ncmFwaGVyUmVmJyB0YXJnZXRNb2R1bGU9J0FkZHJlc3MnPgogICA8bW9kdWxlUmVmZXJlbmNlSXRlbSBtb2R1bGVJdGVtSWQ9JzE0MDc3Mic+CiAgIDwvbW9kdWxlUmVmZXJlbmNlSXRlbT4KPC9tb2R1bGVSZWZlcmVuY2U+Cjx2b2NhYnVsYXJ5UmVmZXJlbmNlIG5hbWU9J011bFR5cGVWb2MnIGlkPSczMDM0MScgaW5zdGFuY2VOYW1lPSdNdWxUeXBlVmdyJyA+CiAgICA8dm9jYWJ1bGFyeVJlZmVyZW5jZUl0ZW0gaWQ9JzEwNTgyOSc+CiAgICA8L3ZvY2FidWxhcnlSZWZlcmVuY2VJdGVtPgo8L3ZvY2FidWxhcnlSZWZlcmVuY2U+Cjxjb21wb3NpdGUgbmFtZT0nTXVsUmVmZXJlbmNlc0NyZSc+CiAgIDxjb21wb3NpdGVJdGVtPgogICAgICAgPG1vZHVsZVJlZmVyZW5jZSBuYW1lPSdNdWxPYmplY3RSZWYnIHRhcmdldE1vZHVsZT0nT2JqZWN0Jz4KICAgICAgICAgICA8bW9kdWxlUmVmZXJlbmNlSXRlbSBtb2R1bGVJdGVtSWQ9JzE1NjYzNDEnPgo8ZGF0YUZpZWxkIG5hbWU9J1RodW1ibmFpbEJvbyc+Cjx2YWx1ZT50cnVlPC92YWx1ZT4KPGZvcm1hdHRlZFZhbHVlIGxhbmd1YWdlPSdlbic+eWVzPC9mb3JtYXR0ZWRWYWx1ZT4KPC9kYXRhRmllbGQ+CiAgICAgICAgICAgPC9tb2R1bGVSZWZlcmVuY2VJdGVtPgogICAgICAgPC9tb2R1bGVSZWZlcmVuY2U+CiAgIDwvY29tcG9zaXRlSXRlbT4KPC9jb21wb3NpdGU+CiAgICAgICAgICAgPC9tb2R1bGVJdGVtPgogICAgICAgPC9tb2R1bGU+CiAgIDwvbW9kdWxlcz4KPC9hcHBsaWNhdGlvbj4K";
$array["moduleId"] = "1566342";
timerStart();
$museum = new Museum();
$museum->setModuleId($array["moduleId"]);
$xml = base64_decode($array["sendMPlus"]);
$museum->setParameter($xml);
echo lapTime() . " s. ";
$ok = $museum->fileDefinitions($xml);
echo lapTime() . " s. ";

function timerStart() {
    global $tstart;
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime [1] + $mtime [0];
    $tstart = $mtime;
}

/**
 * Shows the laptime:
 * echo lapTime();
 */
function lapTime() {
    global $tstart;
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime [1] + $mtime [0];
    $tend = $mtime;
    $tpassed = ($tend - $tstart);
    return round($tpassed, 2) . " sec";
}
