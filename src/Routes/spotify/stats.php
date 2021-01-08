<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use SpotiSync\Constants\Connection;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\SpotifyService;
use SpotiSync\Controllers\SpotifyStatsController;
use SpotiSync\Services\SpotifyAuthService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$connection = Connection::getConnection();
$userRepository = new UserRepository($connection);
$spotifyAuthService = new SpotifyAuthService();
$service = new SpotifyService($spotifyAuthService, $userRepository);
$controller = new SpotifyStatsController($service, $userRepository);

$controller->handleRequest();
