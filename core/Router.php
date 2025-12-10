<?php

class Router {

    private $routes = [];

    public function get($uri, $action) {
        $this->routes['GET'][$uri] = $action;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // remove project folder name if running in localhost
        $uri = str_replace('/bus_booking_system/public', '', $uri);

        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            echo "404 - Page Not Found";
            return;
        }

        list($controller, $method) = explode('@', $this->routes[$method][$uri]);

        require_once __DIR__ . '/../app/controllers/' . $controller . '.php';

        $controllerObj = new $controller;
        $controllerObj->$method();
    }
}
