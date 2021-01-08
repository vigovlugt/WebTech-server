<?php

namespace SpotiSync\Modules\Router;

use SpotiSync\Modules\Router\Models\Request;
use SpotiSync\Modules\Router\Models\Route;

class Router
{
  private array $routes = [];

  public function get($path, $callback)
  {
    $route = new Route("GET", $path, $callback);

    array_push($this->routes, $route);
  }

  public function resolve()
  {
    $request = new Request();
    $method = $request->getMethod();
    $path = $request->getPath();

    $route = $this->findRoute($method, $path);

    echo json_encode($method);
  }

  public function findRoute($method, $path)
  {
    foreach ($this->routes as $route) {
      if ($method === $route->method && $path === $route->path) {
        return $route;
      }
    }

    return null;
  }
}
