<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use Dotenv\Dotenv;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Constants\Connection;
use SpotiSync\Controllers\UserImageController;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$dotenv = Dotenv::createImmutable(__DIR__ . "/../../../");
$dotenv->load();

$connection = Connection::getConnection();
$repository = new UserRepository($connection);
$controller = new UserImageController($repository);

$controller->handle_request();
