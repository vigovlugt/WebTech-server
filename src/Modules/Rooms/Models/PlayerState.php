<?php

namespace SpotiSync\Modules\Rooms\Models;

use React\EventLoop\TimerInterface;
use SpotiSync\Utils\Time;

class PlayerState
{
  public bool $isPlaying = false;
  public ?Track $currentTrack = null;

  public array $queue = [];

  public ?TimerInterface $trackEndTimer = null;
  public int $trackTimeBeforeTimer = 0;
  public int $timerStarted = 0;

  public function getTrackTime()
  {
    return $this->trackTimeBeforeTimer + ($this->isPlaying ? Time::getMs() - $this->timerStarted : 0);
  }

  public function getTimeToTrackEnd()
  {
    return $this->currentTrack->duration - $this->getTrackTime();
  }
}
