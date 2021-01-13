<?php

namespace SpotiSync\Modules\Rooms\Models;

class Album
{
  public string $id;
  public string $name;
  public string $imageUrl;

  public function __construct(object $data)
  {
    $this->id = $data->id;
    $this->name = $data->name;
    $this->imageUrl = $data->images[0]->url;
  }
}
