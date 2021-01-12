<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use SpotiSync\Constants\Connection;
use SpotiSync\Modules\Rooms\Services\RoomService;
use SpotiSync\Modules\Sync\SyncServer;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifyAuthService;
use SpotiSync\Services\SpotifyPlayerService;
use SpotiSync\Services\SpotifyService;

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
$authService = new AuthService($userRepository, $spotifyAuthService, $spotifyService);
$roomService = new RoomService();

$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new SyncServer($spotifyPlayerService, $authService, $userRepository, $roomService),
    )
  ),
  3000
);

$server->run();
