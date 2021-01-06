<?php
    include('connection.php');

    
    $query = $conn->prepare("INSERT INTO spotisync.users (name) VALUES (?);");
    $query->bind_param("s", $name);
    
    echo $_POST['name'];
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
    }
    $query->execute();

?>