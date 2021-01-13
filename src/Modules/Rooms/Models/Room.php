<?php

namespace SpotiSync\Modules\Rooms\Models;

class Room
{
  public int $id;
  public string $name;
  public int $ownerId;

  public array $users = [];

  public PlayerState $playerState;

  function __construct(int $id, string $name, int $ownerId)
  {
    $this->id = $id;
    $this->name = $name;
    $this->ownerId = $ownerId;
    $this->playerState = new PlayerState();
  }

  public function hasUser(int $id)
  {
    foreach ($this->users as $user) {
      if ($user->id == $id) {
        return true;
      }
    }

    return false;
  }

  public function removeUser(int $id)
  {
    $this->users = array_filter($this->users, function (int $userId) use ($id) {
      return $userId !== $id;
    });
  }
}
