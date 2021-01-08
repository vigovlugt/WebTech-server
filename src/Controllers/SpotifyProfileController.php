<?php

namespace SpotiSync\Controllers;

use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifyService;

class SpotifyProfileController
{
  private $service;
  private $repository;

  public function __construct(SpotifyService $service, UserRepository $repository)
  {
    $this->service = $service;
    $this->repository = $repository;
  }

  public function handle_request()
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

  public function get($userId)
  {
    $data = $this->repository->getSpotifyTokens($userId);

    $accessToken = $data["spotifyAccessToken"];

    $spotifyProfile = $this->service->getUserInformation($accessToken);

    return $this->return_json($spotifyProfile);
  }

  public function return_json($data)
  {
    header('Content-Type: application/json');
    echo json_encode($data);
  }
}
