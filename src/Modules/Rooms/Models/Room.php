<?php

namespace SpotiSync\Modules\Rooms\Models;

class Room
{
  public int $id;
  public string $name;
  public int $ownerId;

  public array $users;

  function __construct(int $id, string $name, int $ownerId)
  {
    $this->id = $id;
    $this->name = $name;
    $this->ownerId = $ownerId;
  }
}
