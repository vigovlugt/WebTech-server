<?php

namespace SpotiSync\Services;

use SpotiSync\Models\SpotifyUserTopData;
use SpotiSync\Utils\Requests;

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
