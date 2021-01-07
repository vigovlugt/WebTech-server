<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("constants/connection.php");

require_once("services/AuthService.php");

$repo = new UserRepository($conn);
$spotifyAuthService = new SpotifyAuthService();
$spotifyService = new SpotifyService();

$authService = new AuthService($repo, $spotifyAuthService, $spotifyService);

$authService->startAuthorization();
