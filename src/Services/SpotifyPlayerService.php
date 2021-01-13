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

  public function play(User $user)
  {
    $content = Requests::put("https://api.spotify.com/v1/me/player/play", null, $user->spotifyAccessToken);
    $result = json_decode($content);
    echo $content;

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->play($user);
    }

    return true;
  }
}
