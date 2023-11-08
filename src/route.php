<?php
$router->setNotFound('/src/views/404.php');

$router->get("/","ProductController","index");
$router->get("/category/{id}","CategoryController","id");
$router->get("/product/{slug}","ProductController","slug");