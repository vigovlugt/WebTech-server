<?php

namespace SpotiSync\Modules\Chat\Models;

use DateTime;

class ChatMessage
{
  public int $id;
  public int $userId;
  public string $content;
  public DateTime $created;

  public function __construct(int $id, int $userId, string $content)
  {
    $this->id = $id;
    $this->userId = $userId;
    $this->content = $content;

    $this->created = new DateTime();
  }
}
