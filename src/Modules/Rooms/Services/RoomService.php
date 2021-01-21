<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Modules\Sync\SyncServer;

class RoomService
{
  private array $rooms;

  public SyncServer $syncServer;

  public RoomPlayerService $playerService;
  public RoomQueueService $queueService;
  public RoomContinuousService $roomContinuousService;

  public function __construct(RoomPlayerService $playerService, RoomQueueService $queueService, RoomContinuousService $roomContinuousService)
  {
    $this->playerService = $playerService;
    $this->playerService->setRoomService($this);
    $this->queueService = $queueService;
    $this->queueService->setRoomService($this);
    $this->roomContinuousService = $roomContinuousService;

    $this->rooms = $this->roomContinuousService->getAll();
    foreach ($this->rooms as $room) {
      $this->queueService->sortQueue($room);
    }
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

    $room = new Room($this->getMaxRoomId() + 1, $data->name, $user->user->id);

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
    $this->syncRoom($room);
    $this->syncRooms();
  }

  public function leaveRoom(WsUser $user)
  {
    if (isset($user->roomId)) {
      $userRoom = $this->getRoom($user->roomId);
      $userRoom->removeUser($user->user->id);

      $this->syncRoom($userRoom);
      $this->syncRooms();
    }
  }

  public function syncRoom(Room $room, WsUser $user = null)
  {
    if (isset($user)) {
      $this->syncServer->sendMessage($user, MessageType::$ROOM_SYNC, $room);
      return;
    }

    $this->syncServer->sendMessageToRoom($room->id, MessageType::$ROOM_SYNC, $room);
  }

  public function syncRooms(WsUser $user = null)
  {
    if (isset($user)) {
      $this->syncServer->sendMessage($user, MessageType::$ROOM_LIST_SYNC, $this->rooms);
      return;
    }

    $this->syncServer->sendMessageToAll(MessageType::$ROOM_LIST_SYNC, $this->rooms);
  }

  public function onClose()
  {
    $this->roomContinuousService->saveAll($this->rooms);
  }

  public function getMaxRoomId()
  {
    $max = 0;

    foreach ($this->rooms as $room) {
      if ($room->id > $max) {
        $max = $room->id;
      }
    }

    return $max;
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
