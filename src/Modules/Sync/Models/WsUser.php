<?php

namespace SpotiSync\Modules\Sync\Models;

use Ratchet\ConnectionInterface;
use SpotiSync\Models\User;

class WsUser
{
  public User $user;
  public ?int $roomId;

  public int $socketId;
  public ConnectionInterface $connection;

  public function __construct(User $user, ConnectionInterface $connection)
  {
    $this->user = $user;
    $this->connection = $connection;
    $this->socketId = $connection->resourceId;
  }
}
