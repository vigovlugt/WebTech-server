<?php

namespace SpotiSync\Modules\Router\Models;

class Route
{
  public function __construct(string $method, string $path, $callback)
  {
    $this->method = $method;
    $this->path = $path;
    $this->callback = $callback;
  }

  public string $method;
  public string $path;
  public $callback;
}
