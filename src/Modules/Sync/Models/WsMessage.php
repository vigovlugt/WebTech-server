<?php

namespace SpotiSync\Modules\Sync\Models;

class WsMessage
{
  function __construct(string $type, $data)
  {
    $this->type = $type;
    $this->data = $data;
  }

  public string $type;
  public $data;
}
