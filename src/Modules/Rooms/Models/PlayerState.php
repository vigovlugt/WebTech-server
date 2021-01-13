<?php

namespace SpotiSync\Modules\Rooms\Models;

class PlayerState
{
  public bool $isPlaying = false;
  public ?string $currentTrack = null;

  function __construct()
  {
  }
}
