<?php

namespace SpotiSync\Modules\Rooms\Repositories;

use mysqli;
use SpotiSync\Modules\Rooms\Models\Room;

class RoomRepository
{
  private mysqli $connection;

  public function __construct(mysqli $connection)
  {
    $this->connection = $connection;
  }

  public function getAll()
  {
    $result = $this->connection->query("SELECT * FROM rooms");

    $roomsObjs = [];
    while ($row = $result->fetch_assoc()) {
      array_push($roomsObjs, $row);
    }

    $rooms = [];
    foreach ($roomsObjs as $roomObj) {
      $roomObj["queueTracks"] = $this->getQueueTracks($roomObj["id"]);
      $roomObj["queueVotes"] = $this->getQueueVotes($roomObj["id"]);
      $roomObj["chatMessages"] = $this->getChatMessages($roomObj["id"]);
      array_push($rooms, $roomObj);
    }

    return $rooms;
  }

  public function getQueueTracks(int $roomId)
  {
    $result = $this->connection->query("SELECT * FROM roomQueueTracks WHERE roomId = $roomId");

    $queueTracks = [];
    while ($row = $result->fetch_assoc()) {
      array_push($queueTracks, $row);
    }

    return $queueTracks;
  }

  public function getQueueVotes(int $roomId)
  {
    $result = $this->connection->query("SELECT * FROM roomQueueVotes WHERE roomId = $roomId");

    $queueVotes = [];
    while ($row = $result->fetch_assoc()) {
      array_push($queueVotes, $row);
    }

    return $queueVotes;
  }

  public function getChatMessages(int $roomId)
  {
    $result = $this->connection->query("SELECT * FROM roomChatMessages WHERE roomId = $roomId");

    $chatMessages = [];
    while ($row = $result->fetch_assoc()) {
      array_push($chatMessages, $row);
    }

    return $chatMessages;
  }

  public function create(Room $room)
  {
    // Insert room
    $currentTrackId = isset($room->playerState->currentTrack) ? $room->playerState->currentTrack->track->id : null;
    $currentTrackUserId = isset($room->playerState->currentTrack) ? $room->playerState->currentTrack->userId : null;

    $query = $this->connection->prepare("INSERT INTO rooms (id, name, ownerId, currentTrackId, currentTrackUserId, color) VALUES (?,?,?,?,?,?)");
    $query->bind_param("isisis", $room->id, $room->name, $room->ownerId, $currentTrackId, $currentTrackUserId, $room->color);
    $query->execute();

    $this->insertQueueTracks($room);

    $this->insertQueueVotes($room);

    $this->insertChatMessages($room);
  }

  private function insertQueueTracks(Room $room)
  {
    if (count($room->playerState->queue) === 0) {
      return;
    }
    $queryStr = "INSERT INTO roomQueueTracks (id, roomId, trackId, userId) VALUES ";

    $values = [];
    foreach ($room->playerState->queue as $queueTrack) {
      $trackId = $queueTrack->track->id;
      array_push($values, "('$queueTrack->id', $room->id, '$trackId', $queueTrack->userId)");
    }

    if (count($values) === 0) {
      return;
    }

    $queryStr .= implode(",", $values);

    $query = $this->connection->prepare($queryStr);
    $query->execute();
  }

  private function insertQueueVotes(Room $room)
  {
    foreach ($room->playerState->queue as $queueTrack) {
      $queryStr = "INSERT INTO roomQueueVotes (queueTrackId, roomId, userId, type) VALUES ";

      $values = [];

      foreach ($queueTrack->upvotes as $userId) {
        array_push($values, "($queueTrack->id, $room->id, $userId, 'UP')");
      }

      foreach ($queueTrack->downvotes as $userId) {
        array_push($values, "($queueTrack->id, $room->id, $userId, 'DOWN')");
      }

      if (count($values) === 0) {
        continue;
      }

      $queryStr .= implode(",", $values);

      $query = $this->connection->prepare($queryStr);
      $query->execute();
    }
  }

  private function insertChatMessages(Room $room)
  {
    if (count($room->chat) === 0) {
      return;
    }

    $queryStr = "INSERT INTO roomChatMessages (id, roomId, userId, content) VALUES ";

    $values = [];
    foreach ($room->chat as $chatMessage) {
      $content = $this->connection->real_escape_string($chatMessage->content);
      array_push($values, "($chatMessage->id, $room->id, $chatMessage->userId, '$content')");
    }

    $queryStr .= implode(",", $values);

    $query = $this->connection->prepare($queryStr);
    $query->execute();
  }

  public function deleteAll()
  {
    $queryStr = "DELETE FROM roomChatMessages WHERE 1;\n" .
      "DELETE FROM roomQueueVotes WHERE 1;\n" .
      "DELETE FROM roomQueueTracks WHERE 1;\n" .
      "DELETE FROM rooms WHERE 1;";

    $this->connection->multi_query($queryStr);
    echo $this->connection->error . PHP_EOL;
    while ($this->connection->next_result()) {
    }
  }
}
