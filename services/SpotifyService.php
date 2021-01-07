<?php

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/utils/Requests.php");
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/models/SpotifyUserTopData.php");

class SpotifyService
{
  public function getUserInformation($accessToken)
  {
    $content = Requests::get("https://api.spotify.com/v1/me", $accessToken);
    $result = json_decode($content);

    return $result;
  }

  public function getTopByTypeForPeriod($accessToken, $type, $period)
  {
    $content = Requests::get("https://api.spotify.com/v1/me/top/${type}?time_range=${period}", $accessToken);
    $result = json_decode($content);

    $topData = new SpotifyUserTopData($type, $period, $result);

    return $topData;
  }
}
