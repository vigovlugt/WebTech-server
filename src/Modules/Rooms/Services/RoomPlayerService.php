<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Services\SpotifyPlayerService;
use SpotiSync\Utils\Time;

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
    if ($room->playerState->isPlaying == false) {
      return;
    }

    $room->playerState->trackTimeBeforeTimer = $room->playerState->getTrackTime();

    $room->playerState->isPlaying = false;

    $this->roomService->syncServer->loop->cancelTimer($room->playerState->trackEndTimer);

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

    $currentTrack = $room->playerState->currentTrack;

    if (!isset($currentTrack)) {
      $this->playNext($room);
      return;
    } else {
      // Resume current track

      $this->setTimer($room);
      foreach ($room->users as $user) {
        $this->spotifyPlayerService->play($user, $currentTrack->id, $room->playerState->getTrackTime());
      }
    }

    $room->playerState->isPlaying = true;

    $this->roomService->syncRoom($room);
  }

  public function playNextUser(WsUser $user)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);

    return $this->playNext($room);
  }

  public function playNext(Room $room)
  {
    $room->playerState->trackTimeBeforeTimer = 0;

    if (count($room->playerState->queue) === 0) {
      $room->playerState->currentTrack = null;

      $room->playerState->isPlaying = false;

      $this->roomService->syncRoom($room);

      return;
    }

    // New track
    $room->playerState->currentTrack = array_shift($room->playerState->queue);
    $this->setTimer($room);

    foreach ($room->users as $user) {
      $this->spotifyPlayerService->play($user, $room->playerState->currentTrack->id, $room->playerState->getTrackTime());
    }

    $room->playerState->isPlaying = true;

    $this->roomService->syncRoom($room);
    $this->roomService->syncRooms();
  }

  public function setTimer(Room $room)
  {
    if (isset($room->playerState->trackEndTimer)) {
      $this->roomService->syncServer->loop->cancelTimer($room->playerState->trackEndTimer);
    }

    $room->playerState->timerStarted = Time::getMs();

    $room->playerState->trackEndTimer = $this->roomService->syncServer->loop->addTimer($room->playerState->getTimeToTrackEnd() / 1000, function () use ($room) {
      $this->onSongEnd($room);
    });
  }

  public function onSongEnd(Room $room)
  {
    $this->playNext($room);
  }

  public function syncUserPlayerState(WsUser $user, Room $room)
  {
    $isPlaying = $room->playerState->isPlaying;
    $trackId = null;
    if (isset($room->playerState->currentTrack)) {
      $trackId = $room->playerState->currentTrack->id;
    }

    if ($isPlaying) {
      $this->spotifyPlayerService->play($user->user, $trackId, $room->playerState->getTrackTime());
    } else {
      $this->spotifyPlayerService->pause($user->user);
    }
  }
}
