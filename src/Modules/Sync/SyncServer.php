<?php

namespace SpotiSync\Modules\Sync;

use Exception;
use GuzzleHttp\Psr7\Message;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SpotiSync\Modules\Rooms\Services\RoomService;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsMessage;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;

class SyncServer implements MessageComponentInterface
{
  private array $users = [];

  private AuthService $authService;
  private UserRepository $userRepository;
  private RoomService $roomService;

  public function __construct(AuthService $authService, UserRepository $userRepository, RoomService $roomService)
  {
    $this->authService = $authService;
    $this->userRepository = $userRepository;
    $this->roomService = $roomService;
    $this->roomService->setSyncServer($this);

    echo "server running on 3000\n";
  }

  public function getUser(int $id)
  {
    foreach ($this->users as $user) {
      if ($user->user->id === $id) {
        return $user;
      }
    }

    return null;
  }

  public function getUserBySocketId(int $socketId)
  {
    foreach ($this->users as $user) {
      if ($user->socketId === $socketId) {
        return $user;
      }
    }

    return null;
  }

  public function onMessage(ConnectionInterface $connection, $msg)
  {
    $message = SyncServer::parseMessage($msg);

    $user = $this->getUserBySocketId($connection->resourceId);

    if (!$user) {
      if ($message->type !== "AUTHENTICATE") {
        return;
      }

      $this->handleAuthenticate($connection, $message->data);
      return;
    }

    switch ($message->type) {
      case MessageType::$CREATE_ROOM:
        $this->roomService->createRoom($user, $message->data);
        break;
      case MessageType::$JOIN_ROOM:
        $this->roomService->joinRoom($user, $message->data);
        break;
      case MessageType::$PAUSE_ROOM:
        $this->roomService->pauseRoom($user);
        break;
      case MessageType::$PLAY_ROOM:
        $this->roomService->playRoom($user);
        break;
    }
  }

  public function handleAuthenticate(ConnectionInterface $connection, object $data)
  {
    if (!isset($data->accessToken)) {
      return;
    }

    $userId = $this->authService->getUserId($data->accessToken);
    if (!isset($userId)) {
      return;
    }

    $user = $this->userRepository->get($userId);

    $wsUser = new WsUser($user, $connection);
    array_push($this->users, $wsUser);

    $this->handleConnect($wsUser);
  }

  private function handleConnect(WsUser $wsUser)
  {
    echo "CONNECT: {$wsUser->user->id}\n";

    $this->userRepository->setOnline($wsUser->user->id, true);
    $this->sendMessage($wsUser, "AUTHENTICATED", null);
    $this->roomService->syncRooms($wsUser);
  }

  public function onOpen(ConnectionInterface $connection)
  {
  }

  public function onError(ConnectionInterface $connection, Exception $e)
  {
  }

  public function onClose(ConnectionInterface $connection)
  {
    $user = $this->getUser($connection->resourceId);

    $index = array_search($user, $this->users);

    if ($index !== false) {

      echo "LEAVE: {$user->user->id}\n";

      $this->userRepository->setOnline($user->user->id, false);
      $this->roomService->leaveRoom($user);
    }
  }

  public function sendMessage(WsUser $user, string $type, $data)
  {
    $message = array(
      "type" => $type,
      "data" => $data
    );

    $json = json_encode($message);

    $user->connection->send($json);
  }

  public function sendMessageToAll(string $type, $data)
  {
    foreach ($this->users as $user) {
      $this->sendMessage($user, $type, $data);
    }
  }

  public function sendMessageToRoom(int $roomId, string $type, $data)
  {
    foreach ($this->users as $user) {
      if ($user->roomId === $roomId) {
        $this->sendMessage($user, $type, $data);
      }
    }
  }

  public static function parseMessage(string $message)
  {
    $json = json_decode($message);

    return new WsMessage($json->type, $json->data);
  }
}
