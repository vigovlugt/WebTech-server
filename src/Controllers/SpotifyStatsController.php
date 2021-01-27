<?php

namespace SpotiSync\Controllers;

use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifyService;
use SpotiSync\Models\SpotifyUserStats;

class SpotifyStatsController
{
  private $service;
  private $repository;

  public function __construct(SpotifyService $service, UserRepository $repository)
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
        if (isset($_GET['id'])) {
          return $this->get($_GET['id']);
        }

        return http_response_code(500);
    }
  }

  public function get($userId)
  {
    $user = $this->repository->get($userId);
    $stats = new SpotifyUserStats();
    $stats->medium_tracks = $this->service->getTopByTypeForPeriod($user, "tracks", "medium_term");
    $stats->long_tracks = $this->service->getTopByTypeForPeriod($user, "tracks", "long_term");
    $stats->short_tracks = $this->service->getTopByTypeForPeriod($user, "tracks", "short_term");
    $stats->medium_artists = $this->service->getTopByTypeForPeriod($user, "artists", "medium_term");
    $stats->long_artists = $this->service->getTopByTypeForPeriod($user, "artists", "long_term");
    $stats->short_artists = $this->service->getTopByTypeForPeriod($user, "artists", "short_term");
    $stats->history = $this->service->getHistory($user, "artists", "short_term");

    return $this->return_json($stats);
  }

  public function return_json($data)
  {
    header('Content-Type: application/json');
    echo json_encode($data);
  }
}
