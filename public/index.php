<?php

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once __DIR__ . '/../src/requirements/dispatcher.php';

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        echo '404 Not Found';

        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];

        echo '405 Method Not Allowed';

        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        $request = [];
        $request['param']       = $vars;
        $request['form']        = $_POST;
        $request['queryString'] = $_GET;
        $request['file']        = $_FILES;

        require_once __DIR__ . '/../src/requirements/dependencies.php';

        list($class, $method) = explode(':', $handler, 2);

        echo call_user_func_array(
            array(
                new $class,
                $method
            ),
            [
                $dc,
                $request
            ]
        );

        break;
}
