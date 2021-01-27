<?php

use Dotenv\Dotenv;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use SpotiSync\Constants\Connection;
use SpotiSync\Modules\Chat\Services\RoomChatService;
use SpotiSync\Modules\Rooms\Repositories\RoomRepository;
use SpotiSync\Modules\Rooms\Services\RoomContinuousService;
use SpotiSync\Modules\Rooms\Services\RoomPlayerService;
use SpotiSync\Modules\Rooms\Services\RoomQueueService;
use SpotiSync\Modules\Rooms\Services\RoomService;
use SpotiSync\Modules\Sync\SyncServer;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifyActiveDeviceService;
use SpotiSync\Services\SpotifyAuthService;
use SpotiSync\Services\SpotifyPlayerService;
use SpotiSync\Services\SpotifyService;
use SpotiSync\Services\SpotifyTrackService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require dirname(__DIR__) . '../../../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$dotenv = Dotenv::createImmutable(__DIR__ . "/../../../");
$dotenv->load();

$connection = Connection::getConnection();
$userRepository = new UserRepository($connection);
$roomRepository = new RoomRepository($connection);

$spotifyAuthService = new SpotifyAuthService($userRepository);

$spotifyPlayerService = new SpotifyPlayerService($spotifyAuthService);
$spotifyService = new SpotifyService($spotifyAuthService);
$spotifyTrackService = new SpotifyTrackService($spotifyAuthService);
$spotifyActiveDeviceService = new SpotifyActiveDeviceService($spotifyAuthService);
$authService = new AuthService($userRepository, $spotifyAuthService, $spotifyService);

$roomQueueService = new RoomQueueService($spotifyTrackService);
$roomPlayerService = new RoomPlayerService($spotifyPlayerService, $spotifyActiveDeviceService);
$roomContinuousService = new RoomContinuousService($roomRepository, $spotifyTrackService);
$roomChatService = new RoomChatService();
$roomService = new RoomService($roomPlayerService, $roomQueueService, $roomContinuousService, $roomChatService);

$syncServer = new SyncServer($authService, $userRepository, $roomService);

$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      $syncServer
    )
  ),
  3000
);
$syncServer->setLoop($server->loop);

// Save state on shutdown
function shutdown()
{
  echo PHP_EOL;

  global $roomService;
  $roomService->onClose();
}
register_shutdown_function("shutdown");

$server->loop->addSignal(SIGINT, function () {
  exit();
});

$server->loop->addSignal(SIGTERM, function () {
  exit();
});



$server->run();
