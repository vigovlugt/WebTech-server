<?php

namespace SpotiSync\Controllers;

use SpotiSync\Models\User;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;

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

        return $this->getAll();;
      case "POST":
        return $this->create();
    }
  }

  public function get($id)
  {
    $user = $this->repository->get($id);

    return $this->return_json($user);
  }

  public function getAll()
  {
    $users = $this->repository->getAll();

    return $this->return_json($users);
  }

  public function create()
  {
    $data = json_decode(file_get_contents('php://input'), true);

    $user = new User();
    $user->name = $data["name"];

    $newUser = $this->repository->create($user);

    return $this->return_json($newUser);
  }

  public function return_json($data)
  {
    header('Content-Type: application/json');
    echo json_encode($data);
  }
}
