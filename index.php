<?php
define('ROOT_DIR', __DIR__);

include_once ROOT_DIR . '/config/config.php';
include_once ROOT_DIR . '/core/View.php';

spl_autoload_register(function ($className) {
    if (file_exists(ROOT_DIR . '/src/models/' . $className . '.php')) {
        include_once ROOT_DIR . '/src/models/' . $className . '.php';
    } else if (file_exists(ROOT_DIR . '/src/controllers/' . $className . '.php')) {
        include_once ROOT_DIR . '/src/controllers/' . $className . '.php';
    } else if (file_exists(ROOT_DIR . '/src/' . $className . '.php')) {
        include_once ROOT_DIR . '/src/' . $className . '.php';
    } else if (file_exists(ROOT_DIR . '/core/' . $className . '.php')) {
        include_once ROOT_DIR . '/core/' . $className . '.php';
    }
});

if (array_key_exists('REDIRECT_URL', $_SERVER)) {
    $requestUri = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $url = SSL ? "https://$requestUri" : "http://$requestUri";
    $url = str_replace(BASE_URL, "", $url);
    $url = explode("/", $url);
    $url[0] = "/";
    define("URL", $url);
} else {
    define("URL", ["/"]);
};

$router = new Router();
session_start();
include_once ROOT_DIR . '/src/route.php';
$router->action();