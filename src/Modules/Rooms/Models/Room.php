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

  public function removeUser(int $id)
  {
    $key = null;
    for ($i = 0; $i < count($this->users); $i++) {
      if ($this->users[$i]->id === $id) {
        $key = $i;
      }
    }

    if (isset($key)) {
      unset($this->users[$key]);
      $this->users = array_values($this->users);
    }
  }
}
