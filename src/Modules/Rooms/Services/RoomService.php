<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Chat\Services\RoomChatService;
use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Rooms\ViewModels\RoomViewModel;
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
  public RoomChatService $roomChatService;

  public function __construct(RoomPlayerService $playerService, RoomQueueService $queueService, RoomContinuousService $roomContinuousService, RoomChatService $roomChatService)
  {
    $this->playerService = $playerService;
    $this->playerService->setRoomService($this);
    $this->queueService = $queueService;
    $this->queueService->setRoomService($this);
    $this->roomContinuousService = $roomContinuousService;
    $this->roomChatService = $roomChatService;
    $this->roomChatService->setRoomService($this);

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
    if (!isset($data->name) || trim($data->name) == "") {
      $data->name = "New room";
    }

    $data->name = htmlspecialchars($data->name);

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
      if ($userRoom === null) {
        return;
      }

      $userRoom->removeUser($user->user->id);

      $this->syncRoom($userRoom);
      $this->syncRooms();
    }
  }

  public function deleteRoom(WsUser $user)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->getRoom($user->roomId);
    if ($room->ownerId !== $user->user->id) {
      return;
    }

    $users = $room->users;

    for ($i = 0; $i < count($this->rooms); $i++) {
      if ($this->rooms[$i]->id === $room->id) {
        unset($this->rooms[$i]);
        break;
      }
    }
    $this->rooms = array_values($this->rooms);

    foreach ($users as $user) {
      $this->syncServer->getUser($user->id)->roomId = null;
      $this->syncServer->sendMessageToRoom($room->id, MessageType::$ROOM_SYNC, null);
    }

    $this->syncRooms();
  }

  public function setColor(WsUser $user, object $data)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->getRoom($user->roomId);
    if ($room->ownerId !== $user->user->id) {
      return;
    }

    $room->color = htmlspecialchars($data->color);

    $this->syncRooms();
    $this->syncRoom($room);
  }

  public function syncRoom(Room $room, WsUser $user = null)
  {
    if (isset($user)) {
      $this->syncServer->sendMessage($user, MessageType::$ROOM_SYNC, new RoomViewModel($room));
      return;
    }

    $this->syncServer->sendMessageToRoom($room->id, MessageType::$ROOM_SYNC, new RoomViewModel($room));
  }

  public function syncRooms(WsUser $user = null)
  {
    $roomVms = [];
    foreach ($this->rooms as $room) {
      array_push($roomVms, new RoomViewModel($room));
    }

    if (isset($user)) {
      $this->syncServer->sendMessage($user, MessageType::$ROOM_LIST_SYNC, $roomVms);
      return;
    }

    $this->syncServer->sendMessageToAll(MessageType::$ROOM_LIST_SYNC, $roomVms);
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
