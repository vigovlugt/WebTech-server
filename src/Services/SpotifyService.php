<?php

namespace SpotiSync\Services;

use SpotiSync\Models\SpotifyUserTopData;
use SpotiSync\Utils\Requests;
use SpotiSync\Models\User;
use SpotiSync\Repositories\UserRepository;

class SpotifyService
{
  private SpotifyAuthService $spotifyAuthService;
  private UserRepository $userRepository;

  public function __construct(SpotifyAuthService $spotifyAuthService, UserRepository $userRepository)
  {
    $this->spotifyAuthService = $spotifyAuthService;
    $this->userRepository = $userRepository;
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

    if (SpotifyService::isAccessTokenExpired($result)) {
      $user = $this->refreshUserAccessToken($user);
      return $this->getUserInformation($user);
    }

    return $result;
  }

  public function getTopByTypeForPeriod(User $user, $type, $period)
  {
    $content = Requests::get("https://api.spotify.com/v1/me/top/${type}?time_range=${period}", $user->spotifyAccessToken);
    $result = json_decode($content);

    if (SpotifyService::isAccessTokenExpired($result)) {
      $user = $this->refreshUserAccessToken($user);
      return $this->getTopByTypeForPeriod($user, $type, $period);
    }

    $topData = new SpotifyUserTopData($type, $period, $result);

    return $topData;
  }

  private function refreshUserAccessToken(User $user)
  {
    $newAccessToken = $this->spotifyAuthService->refreshAccessToken($user->spotifyRefreshToken);
    $this->userRepository->setSpotifyTokens($user->id, $newAccessToken, $user->spotifyRefreshToken);
    $user->spotifyAccessToken = $newAccessToken;

    return $user;
  }

  public static function isAccessTokenExpired($result)
  {
    return isset($result->error) && $result->error->status === 401 && $result->error->message === "The access token expired";
  }
}
