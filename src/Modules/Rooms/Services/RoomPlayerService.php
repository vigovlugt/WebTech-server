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
    if ($room->playerState->isPlaying == false) {
      return;
    }

    $room->playerState->isPlaying = false;

    $timerDuration = round(microtime(true) * 1000) - $room->playerState->timerStarted;

    $room->playerState->timeToSongEnd -= $timerDuration;

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

    if (!isset($room->playerState->currentTrack)) {
      $this->playNext($room);
      return;
    } else {
      // Resume current track

      $this->setTimer($room);
      foreach ($room->users as $user) {
        $this->spotifyPlayerService->play($user);
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
    if (count($room->playerState->queue) === 0) {
      $room->playerState->currentTrack = null;

      $room->playerState->isPlaying = false;

      $this->roomService->syncRoom($room);

      return;
    }

    // New track
    $room->playerState->currentTrack = array_shift($room->playerState->queue);

    $room->playerState->timeToSongEnd = $room->playerState->currentTrack->duration;
    $this->setTimer($room);

    foreach ($room->users as $user) {
      $this->spotifyPlayerService->play($user, $room->playerState->currentTrack->id);
    }

    $room->playerState->isPlaying = true;

    $this->roomService->syncRoom($room);
  }

  public function setTimer(Room $room)
  {
    $room->playerState->timerStarted = round(microtime(true) * 1000);

    $room->playerState->trackEndTimer = $this->roomService->syncServer->loop->addTimer($room->playerState->timeToSongEnd / 1000, function () use ($room) {
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

    if ($isPlaying) {
      $this->spotifyPlayerService->play($user->user);
    } else {
      $this->spotifyPlayerService->pause($user->user);
    }
  }
}
