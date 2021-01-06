<?php

require_once("connection.php");

require_once("repositories/UserRepository.php");
require_once("controllers/UserController.php");

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
