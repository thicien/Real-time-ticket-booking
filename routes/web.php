<?php

// Load core router
require_once __DIR__ . '/../core/Router.php';

$router = new Router();

// Routes
$router->get('/', 'HomeController@index');

// Run router
$router->dispatch();
