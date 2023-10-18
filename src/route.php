<?php
$router->setNotFound('/src/views/404.php');

$router->get('/exe-array/bai-3', "DemoController","doGet");
$router->post('/exe-array/bai-3', "DemoController","doPost");