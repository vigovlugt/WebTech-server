<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Models\User;
use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Modules\Sync\SyncServer;

class RoomService
{
  private $rooms = [];

  private int $nextRoomId = 0;

  private SyncServer $syncServer;

  public function setSyncServer(SyncServer $syncServer)
  {
    $this->syncServer = $syncServer;
  }

  public function createRoom(User $user, object $data)
  {
    echo "CREATING NEW ROOM: $data->name\n";

    $room = new Room($this->nextRoomId, $data->name, $user->id);

    $this->nextRoomId++;

    array_push($this->rooms, $room);

    $this->syncServer->sendMessageToAll(MessageType::$ROOM_LIST_SYNC, $this->rooms);
  }

  public function syncRooms(WsUser $user)
  {
    $this->syncServer->sendMessage($user, MessageType::$ROOM_LIST_SYNC, $this->rooms);
  }
}
