<?php
function view($path, $variables = [])
{
    $path = ROOT_DIR . '/src/views/' . $path . '.php';
    extract($variables);
    include ROOT_DIR . '/src/views/index.php';
}

function render($path, $variables = [])
{
    ob_start();
    extract($variables);
    $path = ROOT_DIR . '/src/views/' . $path . '.php';
    return ob_get_clean();
}

