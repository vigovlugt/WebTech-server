<?php

namespace SpotiSync\Modules\Chat\Services;

use SpotiSync\Modules\Chat\Models\ChatMessage;
use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Rooms\Services\RoomService;
use SpotiSync\Modules\Sync\Models\WsUser;

class RoomChatService
{
  private RoomService $roomService;

  public function setRoomService(RoomService $roomService)
  {
    $this->roomService = $roomService;
  }

  public function sendChatMessage(WsUser $user, object $data)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);
    $message = new ChatMessage($this->getMaxChatMessageId($room) + 1, $user->user->id, htmlspecialchars($data->content));

    array_push($room->chat, $message);

    $this->roomService->syncRoom($room);
  }

  public function getMaxChatMessageId(Room $room)
  {
    $max = 0;

    foreach ($room->chat as $chatMessage) {
      if ($chatMessage->id > $max) {
        $max = $chatMessage->id;
      }
    }

    return $max;
  }
}
