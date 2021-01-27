<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use Dotenv\Dotenv;
use SpotiSync\Constants\Connection;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Controllers\SpotifySearchController;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifyAuthService;
use SpotiSync\Services\SpotifySearchService;
use SpotiSync\Services\SpotifyService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$dotenv = Dotenv::createImmutable(__DIR__ . "/../../../");
$dotenv->load();

$connection = Connection::getConnection();
$userRepository = new UserRepository($connection);
$spotifyAuthService = new SpotifyAuthService($userRepository);
$spotifySearchService = new SpotifySearchService($spotifyAuthService);
$spotifyService = new SpotifyService($spotifyAuthService);
$authService = new AuthService($userRepository, $spotifyAuthService, $spotifyService);

$controller = new SpotifySearchController($spotifySearchService, $userRepository);

$controller->handleRequest();
