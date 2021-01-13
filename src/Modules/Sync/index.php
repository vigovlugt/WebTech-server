<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use SpotiSync\Constants\Connection;
use SpotiSync\Modules\Rooms\Services\RoomPlayerService;
use SpotiSync\Modules\Rooms\Services\RoomQueueService;
use SpotiSync\Modules\Rooms\Services\RoomService;
use SpotiSync\Modules\Sync\SyncServer;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
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

$connection = Connection::getConnection();
$userRepository = new UserRepository($connection);
$spotifyAuthService = new SpotifyAuthService($userRepository);
$spotifyPlayerService = new SpotifyPlayerService($spotifyAuthService);
$spotifyService = new SpotifyService($spotifyAuthService);
$spotifyTrackService = new SpotifyTrackService($spotifyAuthService);
$authService = new AuthService($userRepository, $spotifyAuthService, $spotifyService);

$roomQueueService = new RoomQueueService($spotifyTrackService);
$roomPlayerService = new RoomPlayerService($spotifyPlayerService);
$roomService = new RoomService($roomPlayerService, $roomQueueService);

$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new SyncServer($authService, $userRepository, $roomService),
    )
  ),
  3000
);

$server->run();
