<?php
define("ROOT_DIR", __DIR__);
include_once ROOT_DIR . "/config/common.php";
include_once ROOT_DIR . "/config/config.php";

spl_autoload_register(function ($class) {
    if (file_exists(ROOT_DIR . "/core/$class.php")) {
        include_once ROOT_DIR . "/core/$class.php";
    }
    if (file_exists(ROOT_DIR . "/src/controllers/$class.php")) {
        include_once ROOT_DIR . "/src/controllers/$class.php";
    }
    if (file_exists(ROOT_DIR . "/src/models/$class.php")) {
        include_once ROOT_DIR . "/src/models/$class.php";
    }
});

$requestUri = "/";
if (array_key_exists('REDIRECT_URL', $_SERVER)) {
    $serverName = $_SERVER["SERVER_NAME"];
    $requestUri = $_SERVER["REDIRECT_URL"];
    $requestUrl = SSL ? "https://$serverName$requestUri" : "http://$serverName$requestUri";
    $requestUri = str_replace(BASE_URL, "", $requestUrl);
}

include_once ROOT_DIR . "/src/route.php";

session_start();

Router::run($requestUri);
