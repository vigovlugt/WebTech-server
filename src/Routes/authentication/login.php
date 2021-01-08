<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use SpotiSync\Constants\Connection;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\AuthService;
use SpotiSync\Services\SpotifyAuthService;
use SpotiSync\Services\SpotifyService;

$connection = Connection::getConnection();
$repo = new UserRepository($connection);
$spotifyAuthService = new SpotifyAuthService();
$spotifyService = new SpotifyService();

$authService = new AuthService($repo, $spotifyAuthService, $spotifyService);

$authService->startAuthorization();
