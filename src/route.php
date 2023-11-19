<?php

Router::get("/","HomeController@index");
Router::get("/san-pham/{id}","HomeController@id");
Router::get("/danh-muc/{id}","CategoryController@id");