<?php

require_once("../connection.php");

require_once("../services/AuthService.php");

$repo = new UserRepository($conn);
$spotifyAuthService = new SpotifyAuthService();
$spotifyService = new SpotifyService();

$authService = new AuthService($repo, $spotifyAuthService, $spotifyService);

$authService->startAuthorization();
