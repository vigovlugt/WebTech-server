<?php

class UserRepository
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  public function get(int $id)
  {
    $query = mysqli_prepare($this->conn, "SELECT id, name, spotifyId FROM users WHERE id = ?");
    $query->bind_param("i", $id);

    $query->execute();
    $result = $query->get_result();

    return $result->fetch_assoc();
  }

  public function getBySpotifyId($spotifyId)
  {
    $query = mysqli_prepare($this->conn, "SELECT id, name FROM users WHERE spotifyId = ?");
    $query->bind_param("s", $spotifyId);

    $query->execute();
    $result = $query->get_result();

    return $result->fetch_assoc();
  }

  public function getAll()
  {
    $query = mysqli_prepare($this->conn, "SELECT id, name, spotifyId FROM users");
    $query->execute();
    $result = $query->get_result();

    $users = [];

    while ($user = $result->fetch_assoc()) {
      array_push($users, $user);
    }

    return $users;
  }

  public function create(User $user)
  {
    $query = mysqli_prepare($this->conn, "INSERT INTO users (name, spotifyId) VALUES (?, ?)");
    $query->bind_param("ss", $user->name, $user->spotifyId);
    $query->execute();

    $inserted_id = mysqli_insert_id($this->conn);

    return $this->get($inserted_id);
  }
}
