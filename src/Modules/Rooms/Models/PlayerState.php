<?php

namespace SpotiSync\Modules\Rooms\Models;

use React\EventLoop\TimerInterface;

class PlayerState
{
  public bool $isPlaying = false;
  public ?Track $currentTrack = null;

  public array $queue = [];

  public ?TimerInterface $trackEndTimer = null;
  public int $timeToSongEnd = 0;
  public int $timerStarted = 0;
}
