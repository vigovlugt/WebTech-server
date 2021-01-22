<?php

namespace SpotiSync\Controllers;

use SpotiSync\Services\LoggingService;

class LoggingController
{
  private $service;
  private $connection;

  public function __construct(connection $connection, service $service)
  {
    $this->service = $service;
    $this->connection = $connection;
  }

  public function handleRequest()
  {
    $method = $_SERVER['REQUEST_METHOD'];

    $userId = AuthService::getUserId();
    if ($userId == null) {
      return;
    }

    switch ($method) {
      case "GET":
        if (isset($_GET['id'])) {
          return $this->get($_GET['id']);
        }

        return http_response_code(500);
    }
  }
}
