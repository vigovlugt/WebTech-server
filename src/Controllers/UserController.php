<?php

namespace SpotiSync\Controllers;

use SpotiSync\Models\User;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\ViewModels\UserViewModel;

class UserController
{
  private $repository;

  public function __construct(UserRepository $repository)
  {
    $this->repository = $repository;
  }

  public function handle_request()
  {
    $method = $_SERVER['REQUEST_METHOD'];

    $userId = AuthService::getUserId();
    if ($userId == null) {
      echo $userId;
      // http_response_code(401);
      return;
    }

    switch ($method) {
      case "GET":
        if (isset($_GET['id'])) {
          return $this->get($_GET['id']);
        }

        return $this->getAll();
    }
  }

  public function get($id)
  {
    $user = $this->repository->get($id);
    $userViewModel = new UserViewModel($user);

    return $this->return_json($userViewModel);
  }

  public function getAll()
  {
    $users = $this->repository->getAll();
    $userViewModels = [];

    for ($i = 0; $i < count($users); $i++) {
      array_push($userViewModels, new UserViewModel($users[$i]));
    }

    return $this->return_json($userViewModels);
  }

  public function return_json($data)
  {
    header('Content-Type: application/json');
    echo json_encode($data);
  }
}
