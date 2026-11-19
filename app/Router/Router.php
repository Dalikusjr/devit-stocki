<?php

namespace App\Router;

use App\Exceptions\RouteException;

class Router
{
    private array $routes = [];
    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }
    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }
    public function patch(string $uri, array $action): void
    {
        $this->routes['PATCH'][$uri] = $action;
    }
    public function delete(string $uri, array $action): void
    {
        $this->routes['DELETE'][$uri] = $action;
    }
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = preg_replace('#^/html/devitstocki/public/api#', '', $uri);
        if (isset($this->routes[$method][$uri])) {
            [$controllerClass, $methodName] = $this->routes[$method][$uri];
            $controller = new $controllerClass();
            $controller->$methodName();
        } else {
            throw RouteException::RouteNotFoundException('');
        }
    }
}
