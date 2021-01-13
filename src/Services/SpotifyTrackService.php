<?php

namespace SpotiSync\Services;

use SpotiSync\Utils\Requests;
use SpotiSync\Models\User;

class SpotifyTrackService
{
  public function __construct(SpotifyAuthService $spotifyAuthService)
  {
    $this->spotifyAuthService = $spotifyAuthService;
  }

  public function get(User $user, string $id)
  {
    $content = Requests::get("https://api.spotify.com/v1/tracks/$id", $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->get($user, $id);
    }

    return $result;
  }
}
