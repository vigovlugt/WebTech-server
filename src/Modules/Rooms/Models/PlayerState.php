<?php

namespace SpotiSync\Modules\Rooms\Models;

class PlayerState
{
  public bool $isPlaying = false;
  public ?string $currentTrack = null;

  public array $queue = [];

  function __construct()
  {
  }
}
