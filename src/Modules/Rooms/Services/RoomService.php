<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Models\User;
use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Modules\Sync\SyncServer;

class RoomService
{
  private array $rooms = [];

  private int $nextRoomId = 0;

  private SyncServer $syncServer;

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

    $this->syncRooms();
  }

  public function leaveRoom(WsUser $user)
  {
    if (isset($user->roomId)) {
      $userRoom = $this->getRoom($user->roomId);
      $userRoom->removeUser($user->user->id);
    }
  }

  public function syncRooms(WsUser $user = null)
  {
    if (is_null($user)) {
      $this->syncServer->sendMessageToAll(MessageType::$ROOM_LIST_SYNC, $this->rooms);
      return;
    }

    $this->syncServer->sendMessage($user, MessageType::$ROOM_LIST_SYNC, $this->rooms);
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
