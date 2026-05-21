<?php

/**
 * REST service settings file.
 */
date_default_timezone_set('Europe/Helsinki');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if (!defined('ROOT')) {
    define('ROOT', dirname(__FILE__) . '/');
}
/*
  error_reporting(E_ALL | E_STRICT);ini_set("display_errors", "1");
 */
$config = shell_exec("hostname");
$config_file = preg_replace('/\s+/', '', $config);
if (!file_exists(ROOT . "../$config_file.php")) {
    require_once ROOT . "../testiMuseo.php";
} else {
    require_once ROOT . "../$config_file.php";
}


require_once 'rest_functions.php';

if (function_exists("getallheaders")) { //REST pyyntö
    if (isset(getallheaders()["APIKEY"]) && getallheaders()["APIKEY"] != "JustSomething") {
        header("HTTP/1.0 403 Forbidden");
        die();
    }
}

spl_autoload_register(function ($class_name) {
    include ROOT . "oop/$class_name" . ".php";
});
