<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Modules\Sync\SyncServer;

class RoomService
{
  private array $rooms = [];

  private int $nextRoomId = 0;

  public SyncServer $syncServer;

  public RoomPlayerService $playerService;
  public RoomQueueService $queueService;

  public function __construct(RoomPlayerService $playerService, RoomQueueService $queueService)
  {
    $this->playerService = $playerService;
    $this->playerService->setRoomService($this);
    $this->queueService = $queueService;
    $this->queueService->setRoomService($this);
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
    $roomId = $data->id;
    $room = $this->getRoom($roomId);

    $this->leaveRoom($user);

    array_push($room->users, $user->user);
    $user->roomId = $roomId;

    $this->playerService->syncUserPlayerState($user, $room);
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
