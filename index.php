<?php
define('ROOT_DIR', __DIR__);

define('BASE_URL', 'http://127.0.0.1/train-be1-k22');

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
    $temp = explode("/", $_SERVER['REDIRECT_URL']);
    array_splice($temp, 0, 2);
    $temp = array_merge(['/'], $temp);
    define("URL", $temp);
} else {
    define("URL", ["/"]);
};

$router = new Router();
session_start();
include_once ROOT_DIR . '/src/route.php';
$router->action();
