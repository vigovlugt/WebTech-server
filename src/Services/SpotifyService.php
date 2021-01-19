<?php

namespace SpotiSync\Services;

use SpotiSync\Models\SpotifyUserTopData;
use SpotiSync\Utils\Requests;
use SpotiSync\Models\User;

class SpotifyService
{
  private SpotifyAuthService $spotifyAuthService;

  public function __construct(SpotifyAuthService $spotifyAuthService)
  {
    $this->spotifyAuthService = $spotifyAuthService;
  }

  /**
   * Used in authservice before user is known.
   */
  public function getUserInformationByToken($accessToken)
  {
    $content = Requests::get("https://api.spotify.com/v1/me", $accessToken);
    $result = json_decode($content);

    return $result;
  }

  public function getUserInformation(User $user)
  {
    $content = Requests::get("https://api.spotify.com/v1/me", $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->getUserInformation($user);
    }

    return $result;
  }

  public function getTopByTypeForPeriod(User $user, $type, $period)
  {
    $content = Requests::get("https://api.spotify.com/v1/me/top/${type}?time_range=${period}", $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->getTopByTypeForPeriod($user, $type, $period);
    }

    return $result;
  }

  public function getHistory(User $user)
  {
    $content = Requests::get("https://api.spotify.com/v1/me/player/recently-played", $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->getHistory($user);
    }
  

    return $result;
  }
}
