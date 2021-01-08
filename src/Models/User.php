<?php

namespace SpotiSync\Models;

class User
{
  public function __construct(array $data = null)
  {
    $this->id = $data["id"];
    $this->name = $data["name"];

    $this->spotifyId = $data["spotifyId"];
    $this->spotifyAccessToken = $data["spotifyAccessToken"];
    $this->spotifyRefreshToken = $data["spotifyRefreshToken"];
  }

  public $id;
  public $name;

  public $spotifyId;
  public $spotifyAccessToken;
  public $spotifyRefreshToken;
}
