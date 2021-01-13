<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use SpotiSync\Constants\Connection;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Controllers\SpotifySearchController;
use SpotiSync\Services\SpotifyAuthService;
use SpotiSync\Services\SpotifySearchService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$connection = Connection::getConnection();
$userRepository = new UserRepository($connection);
$spotifyAuthService = new SpotifyAuthService($userRepository);
$service = new SpotifySearchService($spotifyAuthService);
$controller = new SpotifySearchController($service, $userRepository);

$controller->handleRequest();
