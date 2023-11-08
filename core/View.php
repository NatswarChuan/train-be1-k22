<?php
function view($path, $variables = [])
{
    $path = ROOT_DIR . '/src/views/' . $path . '.php';
    extract($variables);
    include ROOT_DIR . '/src/views/index.php';
}

function route($routeName)
{
    echo BASE_URL . $routeName;
}

function OK($data)
{
    header("Access-Control-Allow-Origin: " . CORS_ORGIN);
    header("Access-Control-Allow-Headers: " . CORS_HEADER);
    http_response_code(200);
    echo json_encode($data);
    die;
}

function Failed()
{
    header("Access-Control-Allow-Origin: " . CORS_ORGIN);
    header("Access-Control-Allow-Headers: " . CORS_HEADER);
    http_response_code(400);
    die;
}