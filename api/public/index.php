<?php

/**
 * Autor: Jabier Zurro Aduriz
 * Fecha: 02/12/2024
 * Asignatura: DWES
 * UD: 3 - Creación de Servicios Web (APIRest)
 */


//Front Controller: Se encarga de manejar las peticiones de la API.


// Incluir archivos requeridos
require '../core/Router.php';
require '../app/controllers/BookController.php';
require '../app/controllers/ReservationController.php';
require '../app/controllers/UserController.php';

// Crear una instancia del enrutador
$router = new Router();

// Define rutas
$router->add('/public/books', ['controller' => 'BookController', 'action' => 'index'], 'GET');
$router->add('/public/books/{id}', ['controller' => 'BookController', 'action' => 'show'], 'GET');
$router->add('/public/reservations', ['controller' => 'ReservationController', 'action' => 'store'], 'POST');
$router->add('/public/reservations/{id}', ['controller' => 'ReservationController', 'action' => 'update'], 'PUT');
$router->add('/public/reservations/{id}', ['controller' => 'ReservationController', 'action' => 'destroy'], 'DELETE');

// Obtener la URL y el método de la peticion
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Obtener la URL relativa
$urlParams = explode('/', $url);
$urlRelative = '/' . implode('/', array_slice($urlParams, 3));

// Obtener el metodo de la peticion
$method = $_SERVER['REQUEST_METHOD'];

// Manejar la peticion
try {
    $routeDetails = $router->matchRoutes($urlRelative, $method);

    if ($routeDetails === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
        exit;
    }

    $controllerName = $routeDetails['controller'];
    $action = $routeDetails['action'];
    $requestParams = $routeDetails['params'];

    if (!class_exists($controllerName)) {
        throw new Exception("Controller not found");
    }

    $controller = new $controllerName();

    if (!method_exists($controller, $action)) {
        throw new Exception("Method not found");
    }

    call_user_func_array([$controller, $action], $requestParams);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}