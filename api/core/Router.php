<?php

// Clase Router: Clase para manejar las rutas de la API

class Router {
    private $routes = array();

    // Metodo para agregar rutas
    public function add($route, $params, $method) {
        $this->routes[] = ['route' => $route, 'params' => $params, 'method' => $method];
    }
    
    // Metodo para comparar las rutas y comprobar si existen
    public function matchRoutes($url, $method) {
        foreach ($this->routes as $routeDetails) {
            $pattern = str_replace(['{id}', '/'], ['([0-9]+)', '\\/'], $routeDetails['route']);
            if (preg_match("/^$pattern$/", $url, $matches) && $routeDetails['method'] === $method) {
                return [
                    'controller' => $routeDetails['params']['controller'],
                    'action' => $routeDetails['params']['action'],
                    'params' => array_slice($matches, 1) // Captura el {id}
                ];
            }
        }
    
        return null; // Devuelve null si no hay coincidencia
    }        
}