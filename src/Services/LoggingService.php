<?php

namespace SpotiSync\Services;
use mysqli;

class LoggingService
{

  public function __construct(mysqli $connection)
  {
    $this->connection = $connection;
  }

  public function logProfile_watched()
  {
    $query = $this->connection->prepare("INSERT INTO profilesWatched (watcher, watched) VALUES (?, ?)");
    $query->bind_param("ii", $_GET["watcher"], $_GET["watched"]);

    $query->execute();
    if ($query->error) {
      trigger_error($query->error);
    }
    return True;
  }

}
