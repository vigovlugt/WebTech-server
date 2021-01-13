<?php

namespace SpotiSync\Modules\Rooms\Models;

class Artist
{
  public string $id;
  public string $name;

  public function __construct(object $data)
  {
    $this->id = $data->id;
    $this->name = $data->name;
  }
}
