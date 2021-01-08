<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use SpotiSync\Controllers\UserController;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Constants\Connection;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$connection = Connection::getConnection();
$repository = new UserRepository($connection);
$controller = new UserController($repository);

$controller->handle_request();
