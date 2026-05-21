<?php

session_start();
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/config.php';

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

