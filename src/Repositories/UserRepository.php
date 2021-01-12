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

  public function get(int $userId)
  {
    $query = $this->connection->prepare("SELECT * FROM users WHERE id = ?");
    $query->bind_param("i", $userId);

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
    $query = $this->connection->prepare("SELECT * FROM users");

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
    $query = $this->connection->prepare("INSERT INTO users (name, spotifyId, profileImageUrl) VALUES (?, ?, ?)");
    $query->bind_param("sss", $user->name, $user->spotifyId, $user->profileImageUrl);

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
    $query->bind_param("ssi", $accessToken, $refreshToken, $userId);

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }

    return $query->affected_rows > 0;
  }

  public function setOnline($userId, $online = true)
  {
    $query = $this->connection->prepare("UPDATE users SET online=? WHERE id = ?");

    $onlineInt = (int)$online;
    $query->bind_param("ii", $onlineInt, $userId);

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }

    return $query->affected_rows > 0;
  }
}
