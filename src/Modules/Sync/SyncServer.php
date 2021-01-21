<?php

namespace SpotiSync\Modules\Sync;

use Exception;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use SpotiSync\Modules\Rooms\Services\RoomService;
use SpotiSync\Modules\Sync\Constants\MessageType;
use SpotiSync\Modules\Sync\Models\WsMessage;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;

class SyncServer implements MessageComponentInterface
{
  public LoopInterface $loop;

  private AuthService $authService;
  private UserRepository $userRepository;
  private RoomService $roomService;

  private array $users = [];

  public function __construct(AuthService $authService, UserRepository $userRepository, RoomService $roomService)
  {
    $this->authService = $authService;
    $this->userRepository = $userRepository;
    $this->roomService = $roomService;
    $this->roomService->setSyncServer($this);

    echo "server running on 3000\n";
  }

  public function setLoop(LoopInterface $loop)
  {
    $this->loop = $loop;
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
    try {
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
        case MessageType::$ROOM_CREATE:
          $this->roomService->createRoom($user, $message->data);
          break;
        case MessageType::$ROOM_JOIN:
          $this->roomService->joinRoom($user, $message->data);
          break;
        case MessageType::$ROOM_PAUSE:
          $this->roomService->playerService->pauseRoom($user);
          break;
        case MessageType::$ROOM_PLAY:
          $this->roomService->playerService->playRoom($user);
          break;
        case MessageType::$ROOM_ADD_QUEUE:
          $this->roomService->queueService->addToQueue($user, $message->data);
          break;
        case MessageType::$ROOM_PLAY_NEXT:
          $this->roomService->playerService->playNextUser($user);
          break;
        case MessageType::$ROOM_TRACK_UPVOTE:
          $this->roomService->queueService->upvoteTrack($user, $message->data);
          break;
        case MessageType::$ROOM_TRACK_DOWNVOTE:
          $this->roomService->queueService->downvoteTrack($user, $message->data);
          break;
      }
    } catch (\Throwable $th) {
      echo "ERROR ON MESSAGE:\n" . $th . PHP_EOL;
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
    try {
      $user = $this->getUserBySocketId($connection->resourceId);

      $index = array_search($user, $this->users);

      if ($index !== false) {

        echo "LEAVE: {$user->user->id}\n";

        $this->userRepository->setOnline($user->user->id, false);
        $this->roomService->leaveRoom($user);
      }
    } catch (\Throwable $th) {
      echo "ERROR ON CLOSE:\n" . $th . PHP_EOL;
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
      if (isset($user->roomId) && $user->roomId === $roomId) {
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
