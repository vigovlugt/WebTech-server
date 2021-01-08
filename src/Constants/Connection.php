<?php

namespace SpotiSync\Constants;

class Connection
{
  public static function getConnection()
  {
    return mysqli_connect("localhost", "server", "UT7uhM06JFTCJlBD", "spotisync");
  }
}
