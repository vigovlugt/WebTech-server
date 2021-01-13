<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Modules\Sync\SyncServer;
use SpotiSync\Services\SpotifyPlayerService;

class RoomService
{
  private array $rooms = [];

  private int $nextRoomId = 0;

  private SyncServer $syncServer;
  private SpotifyPlayerService $spotifyPlayerService;

  public function __construct(SpotifyPlayerService $spotifyPlayerService)
  {
    $this->spotifyPlayerService = $spotifyPlayerService;
  }

  public function setSyncServer(SyncServer $syncServer)
  {
    $this->syncServer = $syncServer;
  }

  public function createRoom(WsUser $user, object $data)
  {
    if (!isset($data->name)) {
      $data->name = "New room";
    }

    echo "CREATING NEW ROOM: $data->name\n";

    $room = new Room($this->nextRoomId, $data->name, $user->user->id);

    $this->nextRoomId++;

    array_push($this->rooms, $room);

    $this->syncServer->sendMessageToAll(MessageType::$ROOM_LIST_SYNC, $this->rooms);
  }


  public function joinRoom(WsUser $user, object $data)
  {
    $userId = $user->user->id;
    $roomId = $data->id;
    $room = $this->getRoom($roomId);

    $this->leaveRoom($user);

    if (isset($user->roomId)) {
      $userRoom = $this->getRoom($user->roomId);
      $userRoom->removeUser($userId);
    }

    array_push($room->users, $userId);
    $user->roomId = $roomId;

    $this->syncUserPlayerState($user, $room);
    $this->syncRoom($room, $user);
    $this->syncRooms();
  }

  public function leaveRoom(WsUser $user)
  {
    if (isset($user->roomId)) {
      $userRoom = $this->getRoom($user->roomId);
      $userRoom->removeUser($user->user->id);
    }
  }

  public function pauseRoom(WsUser $user)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->getRoom($user->roomId);
    $room->playerState->isPlaying = false;

    foreach ($room->users as $userId) {
      $user = $this->syncServer->getUser($userId);
      $this->spotifyPlayerService->pause($user->user);
    }

    $this->syncRoom($room);
  }

  public function playRoom(WsUser $user)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->getRoom($user->roomId);
    $room->playerState->isPlaying = true;

    foreach ($room->users as $userId) {
      $user = $this->syncServer->getUser($userId);
      $this->spotifyPlayerService->play($user->user);
    }

    $this->syncRoom($room);
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

  public function syncRoom(Room $room, WsUser $user = null)
  {
    if (isset($user)) {
      $this->syncServer->sendMessage($user, MessageType::$SYNC_ROOM, $room);
      return;
    }

    $this->syncServer->sendMessageToRoom($room->id, MessageType::$SYNC_ROOM, $room);
  }

  public function syncRooms(WsUser $user = null)
  {
    if (isset($user)) {
      $this->syncServer->sendMessage($user, MessageType::$ROOM_LIST_SYNC, $this->rooms);
      return;
    }

    $this->syncServer->sendMessageToAll(MessageType::$ROOM_LIST_SYNC, $this->rooms);
  }

  public function getRoom(int $id)
  {
    foreach ($this->rooms as $room) {
      if ($room->id == $id) {
        return $room;
      }
    }

    return null;
  }
}
