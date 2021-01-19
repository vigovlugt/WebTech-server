<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Rooms\Models\QueueTrack;
use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Rooms\Models\Track;
use SpotiSync\Modules\Sync\Models\WsUser;
use SpotiSync\Services\SpotifyTrackService;

class RoomQueueService
{
  private RoomService $roomService;

  private SpotifyTrackService $spotifyTrackService;

  private int $nextQueueTrackId = 0;

  public function __construct(SpotifyTrackService $spotifyTrackService)
  {
    $this->spotifyTrackService = $spotifyTrackService;
  }

  public function setRoomService(RoomService $roomService)
  {
    $this->roomService = $roomService;
  }

  public function addToQueue(WsUser $user, object $data)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);

    $track = new Track($this->spotifyTrackService->get($user->user, $data->id));

    $queueTrack = new QueueTrack($this->nextQueueTrackId++, $user->user->id, $track);

    array_push($room->playerState->queue, $queueTrack);

    $this->sortQueue($room);

    $this->roomService->syncRoom($room);
  }

  public function upvoteTrack(WsUser $user, object $data)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);

    $track = $this->getQueueTrack($room, $data->id);
    if (!$track) {
      return;
    }

    if (array_search($user->user->id, $track->upvotes) !== false) {
      return;
    }

    array_push($track->upvotes, $user->user->id);
    $track->downvotes = array_diff($track->downvotes, array($user->user->id));

    $this->sortQueue($room);

    $this->roomService->syncRoom($room);
  }

  public function downvoteTrack(WsUser $user, object $data)
  {
    if (!isset($user->roomId)) {
      return;
    }

    $room = $this->roomService->getRoom($user->roomId);

    $track = $this->getQueueTrack($room, $data->id);
    if (!$track) {
      return;
    }

    if (array_search($user->user->id, $track->downvotes) !== false) {
      return;
    }

    array_push($track->downvotes, $user->user->id);
    $track->upvotes = array_diff($track->upvotes, array($user->user->id));

    $this->sortQueue($room);

    $this->roomService->syncRoom($room);
  }

  private function sortQueue(Room $room)
  {
    usort($room->playerState->queue, function (QueueTrack $a, QueueTrack $b) {
      if ($a->getVotes() === $b->getVotes()) {
        return $a->id <=> $b->id;
      }

      return $b->getVotes() <=> $a->getVotes();
    });
  }

  private function getQueueTrack(Room $room, int $id)
  {
    foreach ($room->playerState->queue as $queueTrack) {
      if ($queueTrack->id == $id) {
        return $queueTrack;
      }
    }
  }
}
