<?php
session_start();

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


if (!isset($_SESSION['user'])) {
    header("Location: $loginUri");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fi">
    <head>
        <meta charset="UTF-8">
        <title>Entra kirjautuminen</title>
    </head>
    <body>
        <h1>Tervetuloa!</h1>
        <p>Nimi: <?= htmlspecialchars($user['name']) ?></p>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p><a href="logout.php">Kirjaudu ulos</a></p>
    </body>
</html>

