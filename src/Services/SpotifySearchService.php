<?php

namespace SpotiSync\Services;

use SpotiSync\Utils\Requests;
use SpotiSync\Models\User;

class SpotifySearchService
{
  public function __construct(SpotifyAuthService $spotifyAuthService)
  {
    $this->spotifyAuthService = $spotifyAuthService;
  }

  public function search(User $user, string $query)
  {
    $query = urlencode($query);
    $content = Requests::get("https://api.spotify.com/v1/search?q=$query&type=track", $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->search($user, $query);
    }

    return $result;
  }
}
