<?php

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/utils/Requests.php");

class SpotifyService
{
  public function getUserInformation($accessToken)
  {
    $content = Requests::get("https://api.spotify.com/v1/me", $accessToken);
    $result = json_decode($content);

    return $result;
  }
}
