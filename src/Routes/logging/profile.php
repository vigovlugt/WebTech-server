<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../vendor/autoload.php";

use SpotiSync\Constants\Connection;
use SpotiSync\Controllers\LoggingController;
use SpotiSync\Services\LoggingService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$connection = Connection::getConnection();
$service = new LoggingService();
$controller = new LoggingController($connection, $service);

$controller->handleRequest();
