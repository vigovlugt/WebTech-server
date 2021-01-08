<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use SpotiSync\Constants\Connection;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Services\SpotifyService;
use SpotiSync\Controllers\SpotifyStatsController;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$connection = Connection::getConnection();
$repository = new UserRepository($connection);
$service = new SpotifyService();
$controller = new SpotifyStatsController($service, $repository);

$controller->handle_request();
