<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Services\SpotifyPlayerService;

class RoomPlayerService
{
  private RoomService $roomService;

  private SpotifyPlayerService $spotifyPlayerService;

  public function __construct(SpotifyPlayerService $spotifyPlayerService)
  {
    $this->spotifyPlayerService = $spotifyPlayerService;
  }

  public function setRoomService(RoomService $roomService)
  {
    $this->roomService = $roomService;
  }

  public function pauseRoom(WsUser $user)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);
    $room->playerState->isPlaying = false;

    foreach ($room->users as $user) {
      $this->spotifyPlayerService->pause($user);
    }

    $this->roomService->syncRoom($room);
  }

  public function playRoom(WsUser $user)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);
    $room->playerState->isPlaying = true;

    foreach ($room->users as $user) {
      $this->spotifyPlayerService->play($user);
    }

    $this->roomService->syncRoom($room);
  }

  public function syncUserPlayerState(WsUser $user, Room $room)
  {
    $isPlaying = $room->playerState->isPlaying;

    if ($isPlaying) {
      $this->spotifyPlayerService->play($user->user);
    } else {
      $this->spotifyPlayerService->pause($user->user);
    }
  }
}
