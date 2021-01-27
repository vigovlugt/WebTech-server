<?php

namespace SpotiSync\Modules\Rooms\Services;

use SpotiSync\Modules\Chat\Models\ChatMessage;
use SpotiSync\Modules\Rooms\Models\QueueTrack;
use SpotiSync\Modules\Rooms\Models\Room;
use SpotiSync\Modules\Rooms\Models\Track;
use SpotiSync\Modules\Rooms\Repositories\RoomRepository;
use SpotiSync\Services\SpotifyTrackService;

class RoomContinuousService
{
  private RoomRepository $roomRepository;
  private SpotifyTrackService $spotifyTrackService;

  public function __construct(RoomRepository $roomRepository, SpotifyTrackService $spotifyTrackService)
  {
    $this->roomRepository = $roomRepository;
    $this->spotifyTrackService = $spotifyTrackService;
  }

  public function getAll()
  {
    $roomObjs = $this->roomRepository->getAll();

    $trackDict = $this->getAllTrackData($roomObjs);

    $rooms = [];

    foreach ($roomObjs as $roomObj) {
      $room = new Room($roomObj["id"], $roomObj["name"], $roomObj["ownerId"]);
      $room->color = $roomObj["color"];

      if (isset($roomObj["currentTrackId"]) && !empty($roomObj["currentTrackId"])) {
        $room->playerState->currentTrack = new QueueTrack(-1, $roomObj["currentTrackUserId"], $trackDict[$roomObj["currentTrackId"]]);
      }

      $this->setRoomQueueTracks($room, $roomObj["queueTracks"], $trackDict);

      $this->setRoomQueueVotes($room, $roomObj["queueVotes"]);

      $this->setRoomChatMessages($room, $roomObj["chatMessages"]);

      array_push($rooms, $room);
    }

    return $rooms;
  }

  private function setRoomQueueTracks(Room $room, array $queueTracks, array $trackDict)
  {
    foreach ($queueTracks as $queueTrackObj) {
      $track = $trackDict[$queueTrackObj["trackId"]];
      $queueTrack = new QueueTrack($queueTrackObj["id"], $queueTrackObj["userId"], $track);
      array_push($room->playerState->queue, $queueTrack);
    }
  }

  private function setRoomQueueVotes(Room $room, array $queueVotes)
  {
    foreach ($queueVotes as $queueVoteObj) {
      foreach ($room->playerState->queue as $queueTrack) {
        if ($queueTrack->id == $queueVoteObj["queueTrackId"] && $room->id == $queueVoteObj["roomId"]) {
          if ($queueVoteObj["type"] == "UP") {
            array_push($queueTrack->upvotes, intval($queueVoteObj["userId"]));
          } else if ($queueVoteObj["type"] == "DOWN") {
            array_push($queueTrack->downvotes, intval($queueVoteObj["userId"]));
          }
          break;
        }
      }
    }
  }

  private function setRoomChatMessages(Room $room, array $chatMessages)
  {
    foreach ($chatMessages as $chatMessage) {
      $chatMessage = new ChatMessage($chatMessage["id"], $chatMessage["userId"], $chatMessage["content"]);
      array_push($room->chat, $chatMessage);
    }
  }

  private function getAllTrackData(array $roomObjs)
  {
    $ids = $this->getAllTrackIds($roomObjs);

    $tracks = $this->spotifyTrackService->getTracks($ids);

    $trackDict = [];

    foreach ($tracks as $data) {
      $track = new Track($data);
      $trackDict[$track->id] = $track;
    }

    return $trackDict;
  }

  private function getAllTrackIds(array $roomObjs)
  {
    $ids = [];

    foreach ($roomObjs as $roomObj) {
      if (isset($roomObj["currentTrackId"])) {
        array_push($ids, $roomObj["currentTrackId"]);
      }

      foreach ($roomObj["queueTracks"] as $queueTrack) {
        array_push($ids, $queueTrack["trackId"]);
      }
    }

    return $ids;
  }

  public function saveAll(array $rooms)
  {
    $this->roomRepository->deleteAll();

    foreach ($rooms as $room) {
      $this->roomRepository->create($room);
    }
  }
}
