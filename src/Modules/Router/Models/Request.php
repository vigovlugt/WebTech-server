<?php

namespace SpotiSync\Modules\Router\Models;

class Request
{
  public function getPath()
  {
    $path = $_SERVER["REQUEST_URI"];
    $queryStringPos = strpos($path, "?");
    if ($queryStringPos === false) {
      return $path;
    }

    return substr($path, 0, $queryStringPos);
  }

  public function getMethod()
  {
    return $_SERVER['REQUEST_METHOD'];
  }
}
