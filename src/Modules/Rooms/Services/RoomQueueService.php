<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\Track;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Services\SpotifyTrackService;

class RoomQueueService
{
  private RoomService $roomService;

  private SpotifyTrackService $spotifyTrackService;

  public function __construct(SpotifyTrackService $spotifyTrackService)
  {
    $this->spotifyTrackService = $spotifyTrackService;
  }

  public function setRoomService(RoomService $roomService)
  {
    $this->roomService = $roomService;
  }

  public function addToQueue(WsUser $user, object $data)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);

    $track = new Track($this->spotifyTrackService->get($user->user, $data->id));

    array_push($room->playerState->queue, $track);

    $this->roomService->syncRoom($room);
  }
}
