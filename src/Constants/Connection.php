<?php

namespace SpotiSync\Constants;

class Connection
{
  public static function getConnection()
  {
    return mysqli_connect($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_DATABASE"]);
  }
}
