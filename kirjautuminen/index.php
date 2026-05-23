<?php

// 1. Configure session cookie params for the whole root path
session_set_cookie_params([
    'path' => '/',
    'samesite' => 'Lax'
]);

// 2. Start the session once right here
session_start();

require __DIR__ . '/../vendor/autoload.php';
if (!defined('ROOT')) {
    define('ROOT', dirname(__FILE__) . '/../');
}

$config = shell_exec("hostname");
$config_file = preg_replace('/\s+/', '', $config);
if (!file_exists(ROOT . $config_file . ".php")) {
    require_once ROOT . "testiMuseo.php";
    $test = true;
} else {
    require_once ROOT . "$config_file.php";
}

use Jumbojett\OpenIDConnectClient;

$oidc = new OpenIDConnectClient($issuer, $clientId, $clientSecret);
$oidc->setRedirectURL($redirectUri);
$oidc->addScope(['openid', 'profile', 'email']);

// 3. Clean local development bypass
if (
    defined('TEST_SERVER') &&
    TEST_SERVER === true &&
    getenv('APP_ENV') === 'local'
) {
    // Inject the exact structure the application wants
    $_SESSION['user'] = [
        'name'         => 'Lokaali Kehittäjä',
        'email'        => 'testi@museovirasto.fi',
        'id_token'     => 'mock-id-token',
        'access_token' => 'mock-access-token'
    ];

    // Force PHP to write it immediately to storage
    session_write_close();

    // Perform the clean redirect
    header("Location: " . WEBROOT);
    exit;
} else {
    // Original Microsoft Auth Logic for Production/Staging:
    $oidc->setVerifyHost(false);
    $oidc->setVerifyPeer(false);
    $oidc->authenticate();

    $_SESSION['user'] = [
        'name' => $oidc->requestUserInfo('name'),
        'email' => $oidc->requestUserInfo('email'),
        'id_token' => $oidc->getIdToken(),
        'access_token' => $oidc->getAccessToken(),
    ];

    header("Location: " . WEBROOT);
    exit;
}
