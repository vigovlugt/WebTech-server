<?php

class SpotifyUserTopData
{
  public function __construct($type, $period, $data)
  {
    $this->type = $type;
    $this->period = $period;
    $this->data = $data;
  }

  public $type;
  public $period;
  public $data;
}
