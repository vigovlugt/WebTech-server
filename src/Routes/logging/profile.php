<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use Dotenv\Dotenv;
use SpotiSync\Constants\Connection;
use SpotiSync\Services\LoggingService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$dotenv = Dotenv::createImmutable(__DIR__ . "/../../../");
$dotenv->load();

$connection = Connection::getConnection();
$service = new LoggingService($connection);
$service->logProfile_watched();
