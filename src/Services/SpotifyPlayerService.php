<?php

namespace SpotiSync\Services;

use SpotiSync\Utils\Requests;
use SpotiSync\Models\User;

class SpotifyPlayerService
{
  public function __construct(SpotifyAuthService $spotifyAuthService)
  {
    $this->spotifyAuthService = $spotifyAuthService;
  }

  public function getPlayerInfo(User $user)
  {
    $content = Requests::get("https://api.spotify.com/v1/me/player", null, $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->getPlayerInfo($user);
    }

    return $result;
  }

  public function pause(User $user)
  {
    $content = Requests::put("https://api.spotify.com/v1/me/player/pause", null, $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->pause($user);
    }

    return true;
  }

  public function play(User $user, string $trackId = null, int $position = null, string $deviceId = null)
  {
    $body = null;

    if (isset($trackId)) {
      $body = array(
        "uris" => array("spotify:track:$trackId"),
        "position_ms" => $position
      );
    }

    $content = Requests::put("https://api.spotify.com/v1/me/player/play" . (isset($deviceId) ? "?device_id=$deviceId" : ""), $body, $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->play($user, $trackId, $position);
    }

    return $result;
  }

  public function hasNoActiveDevice(?object $response)
  {
    return isset($response) && isset($response->error) && $response->error->status === 404 && $response->error->reason === "NO_ACTIVE_DEVICE";
  }
}
