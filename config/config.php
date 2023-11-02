<?php
define('DB_HOST','127.0.0.1');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','be1');
define('DB_PORT','3306');

define('BASE_URL', 'http://127.0.0.1/train-be1-k22');
define('SSL', false);

define('NAV_BAR', ROOT_DIR.'/src/views/nav-bar.php');
define('FOOTER', ROOT_DIR.'/src/views/footer.php');
define('HEADER', ROOT_DIR.'/src/views/header.php');
define('PAGINATION', ROOT_DIR . '/src/views/pagination.php');

define('CATALOG_PER_PAGE', 9);
define('BLOG_PER_PAGE', 3);
define('CORS_ORGIN', '*');
define('CORS_HEADER', '*');
define("DATA_TYPE_MAPPINGS",[
    "int" => "i",
    "tinyint" => "i",
    "smallint" => "i",
    "mediumint" => "i",
    "bigint" => "i",
    "float" => "d",
    "double" => "d",
    "decimal" => "d",
    "date" => "s",
    "time" => "s",
    "datetime" => "s",
    "timestamp" => "s",
    "boolean" => "i",
    "enum" => "s",
    "set" => "s",
    "varchar" => "s",
    "text" => "s",
    "blob" => "b",
    "longblob" => "b",
    "geometry" => "b"
]);


function dd(...$agrs){
    var_dump(...$agrs);
    die;
}