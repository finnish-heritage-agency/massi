<?php

/**
 * Selaimella
 */
require_once '../rest/rest_settings.php';

$museum = new Auth();
$museum->login();

if ($museum->getLoginStatus() > 0) {
    echo "ALL GOOD \n";
} else {
    debug($museum);
    echo "Username and password is wrong\n";
    die();
}
echo "Removing sessionkey --> ";

unlink($museum->getSessionKey());
echo "removed --> \n";

$museum->login();

if ($museum->getLoginStatus() > 0) {
    echo "ALL IS GOOD AGAIN\n";
}

