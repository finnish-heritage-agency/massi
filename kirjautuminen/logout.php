<?php

session_start();
session_destroy();

if (!defined('ROOT')) {
    define('ROOT', dirname(__FILE__) . '/../');
}

$configFile = $_ENV['APP_CONFIG'] ?? '';

if (
    $configFile !== '' &&
    file_exists(ROOT . $configFile . '.php')
) {
    require_once ROOT . $configFile . '.php';
} else {
    $config = shell_exec("hostname");
    $config_file = preg_replace('/\s+/', '', $config);

    if (!file_exists(ROOT . $config_file . ".php")) {
        require_once ROOT . "testiMuseo.php";
        $test = true;
    } else {
        require_once ROOT . $config_file . ".php";
    }
}

// Voit ohjata myös Microsoftin logouttiin jos haluat:
$logoutUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/logout";

header("Location: $logoutUrl?post_logout_redirect_uri=" . urlencode($loginUri));
exit;

