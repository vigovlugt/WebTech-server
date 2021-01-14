<?php

namespace SpotiSync\Modules\Rooms\Models;

class Track
{
  public string $id;
  public string $name;
  public Artist $artist;
  public Album $album;
  public int $duration;

  public function __construct(object $data)
  {
    $this->id = $data->id;
    $this->name = $data->name;
    $this->artist = new Artist($data->artists[0]);
    $this->album = new Album($data->album);
    $this->duration = $data->duration_ms;
  }
}
