<?php
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/./constants/connection.php");

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/./repositories/UserRepository.php");
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/./controllers/UserController.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$repository = new UserRepository($conn);
$controller = new UserController($repository);

$controller->handle_request();
    
    // $query = $conn->prepare("INSERT INTO spotisync.users (name) VALUES (?);");
    // $query->bind_param("s", $name);
    
    // echo $_POST['name'];
    // if (isset($_POST['name'])) {
    //     $name = $_POST['name'];
    // }
    // $query->execute();
