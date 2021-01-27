<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use Dotenv\Dotenv;
use SpotiSync\Constants\Connection;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifyAuthService;
use SpotiSync\Services\SpotifyService;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../../../");
$dotenv->load();

$connection = Connection::getConnection();
$userRepository = new UserRepository($connection);
$spotifyAuthService = new SpotifyAuthService($userRepository);
$spotifyService = new SpotifyService($spotifyAuthService);

$authService = new AuthService($userRepository, $spotifyAuthService, $spotifyService);

$authService->startAuthorization();
