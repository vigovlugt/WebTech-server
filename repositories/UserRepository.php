<?php

namespace Repositories;

use Models\User;
use mysqli;

class UserRepository
{
  private mysqli $conn;

  private function __construct(mysqli $conn)
  {
    $this->conn = $conn;
  }

  public function get(int $id)
  {
    $query = mysqli_prepare($this->conn, "SELECT id, name FROM spotisync.users WHERE id = ?");
    $query->bind_param("i", $id);

    $result = $query->get_result();

    $user = $result->fetch_assoc();

    echo $user["name"];
  }

  public function getAll()
  {
  }

  public function create(User $user)
  {
  }
}
