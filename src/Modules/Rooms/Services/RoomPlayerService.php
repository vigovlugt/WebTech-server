<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Services\SpotifyActiveDeviceService;
use SpotiSync\Services\SpotifyPlayerService;
use SpotiSync\Utils\Time;

class RoomPlayerService
{
  private RoomService $roomService;

  private SpotifyPlayerService $spotifyPlayerService;
  private SpotifyActiveDeviceService $spotifyActiveDeviceService;

  public function __construct(SpotifyPlayerService $spotifyPlayerService, SpotifyActiveDeviceService $spotifyActiveDeviceService)
  {
    $this->spotifyPlayerService = $spotifyPlayerService;
    $this->spotifyActiveDeviceService = $spotifyActiveDeviceService;
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
        $response = $this->spotifyPlayerService->play($user, $currentTrack->track->id, $room->playerState->getTrackTime());

        if ($this->spotifyPlayerService->hasNoActiveDevice($response)) {
          $availableDevices = $this->spotifyActiveDeviceService->getAvailableDevices($user);

          $wsUser = $this->roomService->syncServer->getUser($user->id);

          $this->roomService->syncServer->sendMessage($wsUser, MessageType::$AVAILABLE_DEVICES, $availableDevices);
        }
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
      $this->roomService->syncRooms();

      return;
    }

    // New track
    $room->playerState->currentTrack = array_shift($room->playerState->queue);
    $this->setTimer($room);

    foreach ($room->users as $user) {
      $response = $this->spotifyPlayerService->play($user, $room->playerState->currentTrack->track->id, $room->playerState->getTrackTime());

      if ($this->spotifyPlayerService->hasNoActiveDevice($response)) {
        $availableDevices = $this->spotifyActiveDeviceService->getAvailableDevices($user);

        $wsUser = $this->roomService->syncServer->getUser($user->id);

        $this->roomService->syncServer->sendMessage($wsUser, MessageType::$AVAILABLE_DEVICES, $availableDevices);
      }
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

  public function syncUserPlayerState(WsUser $user, Room $room, string $deviceId = null)
  {
    $isPlaying = $room->playerState->isPlaying;
    $trackId = null;
    if (isset($room->playerState->currentTrack)) {
      $trackId = $room->playerState->currentTrack->track->id;
    }

    if ($isPlaying) {
      $response = $this->spotifyPlayerService->play($user->user, $trackId, $room->playerState->getTrackTime(), $deviceId);

      if ($this->spotifyPlayerService->hasNoActiveDevice($response)) {
        $availableDevices = $this->spotifyActiveDeviceService->getAvailableDevices($user->user);

        $this->roomService->syncServer->sendMessage($user, MessageType::$AVAILABLE_DEVICES, $availableDevices);
      }
    } else {
      $this->spotifyPlayerService->pause($user->user);
    }
  }

  public function setActiveDevice(WsUser $user, object $data)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);

    $this->syncUserPlayerState($user, $room, $data->id);
  }
}
