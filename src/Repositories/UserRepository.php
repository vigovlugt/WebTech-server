<?php

namespace SpotiSync\Repositories;

use mysqli;
use SpotiSync\Models\User;

class UserRepository
{
  private $connection;

  public function __construct(mysqli $connection)
  {
    $this->connection = $connection;
  }

  public function get(int $id)
  {
    $query = $this->connection->prepare("SELECT * FROM users WHERE id = ?");
    $query->bind_param("i", $id);

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }

    $result = $query->get_result();

    $data = $result->fetch_assoc();
    if ($data === null) {
      return null;
    }

    return new User($data);
  }

  public function getBySpotifyId($spotifyId)
  {
    $query = $this->connection->prepare("SELECT id, name, spotifyId FROM users WHERE spotifyId = ?");
    $query->bind_param("s", $spotifyId);

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }

    $result = $query->get_result();

    $data = $result->fetch_assoc();
    if ($data === null) {
      return null;
    }

    return new User($data);
  }

  public function getAll()
  {
    $query = $this->connection->prepare("SELECT id, name, spotifyId FROM users");

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }

    $result = $query->get_result();

    $users = [];

    while ($user = $result->fetch_assoc()) {
      array_push($users, new User($user));
    }

    return $users;
  }

  public function create(User $user)
  {
    $query = $this->connection->prepare("INSERT INTO users (name, spotifyId) VALUES (?, ?)");
    $query->bind_param("ss", $user->name, $user->spotifyId);

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }

    $inserted_id = mysqli_insert_id($this->connection);
    return $this->get($inserted_id);
  }

  public function setSpotifyTokens($userId, $accessToken, $refreshToken)
  {
    $query = $this->connection->prepare("UPDATE users SET spotifyAccessToken=?, spotifyRefreshToken=? WHERE id = ?");
    echo $this->connection->error;

    $query->bind_param("ssi", $accessToken, $refreshToken, $userId);

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }

    return $query->affected_rows > 0;
  }
}
