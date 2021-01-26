<?php

namespace SpotiSync\Modules\Rooms\ViewModels;

use SpotiSync\Modules\Rooms\Models\PlayerState;
use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\ViewModels\UserViewModel;

class RoomViewModel
{
  public int $id;
  public string $name;
  public string $color;
  public int $ownerId;

  public array $users = [];

  public PlayerState $playerState;

  public array $chat;

  function __construct(Room $room)
  {
    $this->id = $room->id;
    $this->name = $room->name;
    $this->ownerId = $room->ownerId;
    $this->playerState = $room->playerState;
    $this->color = $room->color;
    $this->chat = $room->chat;

    foreach ($room->users as $user) {
      array_push($this->users, new UserViewModel($user));
    }
  }
}
