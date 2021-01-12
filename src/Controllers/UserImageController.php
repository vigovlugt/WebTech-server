<?php

namespace SpotiSync\Controllers;

use SpotiSync\Repositories\UserRepository;

class UserImageController
{
  private $repository;

  public function __construct(UserRepository $repository)
  {
    $this->repository = $repository;
  }

  public function handle_request()
  {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
      case "GET":
        if (isset($_GET['id'])) {
          return $this->get($_GET['id']);
        }
    }
  }

  public function get($id)
  {
    $user = $this->repository->get($id);

    $image = file_get_contents($user->profileImageUrl);

    header('Content-type: image/jpeg;');
    header("Content-Length: " . strlen($image));
    echo $image;
  }
}
