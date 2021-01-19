<?php

namespace SpotiSync\Modules\Rooms\Models;

class QueueTrack
{
  public int $id;
  public int $userId;
  public Track $track;

  public array $upvotes = [];
  public array $downvotes = [];

  public function __construct(int $id, int $userId, Track $track)
  {
    $this->id = $id;
    $this->userId = $userId;
    $this->track = $track;

    array_push($this->upvotes, $userId);
  }

  public function getVotes()
  {
    return count($this->upvotes) - count($this->downvotes);
  }
}
