<?php

session_start();
require __DIR__ . '/../vendor/autoload.php';
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

use Jumbojett\OpenIDConnectClient;

$oidc = new OpenIDConnectClient($issuer, $clientId, $clientSecret);
$oidc->setRedirectURL($redirectUri);
$oidc->addScope(['openid', 'profile', 'email']);
$oidc->authenticate();

$_SESSION['user'] = [
    'name' => $oidc->requestUserInfo('name'),
    'email' => $oidc->requestUserInfo('email'),
    'id_token' => $oidc->getIdToken(),
    'access_token' => $oidc->getAccessToken(),
];

header("Location: " . WEBROOT);
//header('Location: protected.php');
exit;