<?php

Router::get("/","ProductController@index");
Router::get("/category/{id}","CategoryController@id");
Router::get("/product/{slug}","ProductController@slug");