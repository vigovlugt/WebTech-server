<?php

namespace SpotiSync\Controllers;

use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifySearchService;

class SpotifySearchController
{
  private $service;
  private $repository;

  public function __construct(SpotifySearchService $service, UserRepository $repository)
  {
    $this->service = $service;
    $this->repository = $repository;
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
        if (isset($_GET['q'])) {
          return $this->get($userId, $_GET['q']);
        }

        return http_response_code(500);
    }
  }

  public function get($userId, $query)
  {
    $user = $this->repository->get($userId);

    $result = $this->service->search($user, $query);

    return $this->return_json($result);
  }

  public function return_json($json)
  {
    header('Content-Type: application/json');
    echo json_encode($json);
  }
}
