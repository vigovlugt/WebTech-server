<?php

namespace SpotiSync\Services;

use SpotiSync\Utils\Requests;
use SpotiSync\Models\User;

class SpotifyActiveDeviceService
{
  private SpotifyAuthService $spotifyAuthService;

  public function __construct(SpotifyAuthService $spotifyAuthService)
  {
    $this->spotifyAuthService = $spotifyAuthService;
  }

  public function getAvailableDevices(User $user)
  {
    $content = Requests::get("https://api.spotify.com/v1/me/player/devices", $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->getAvailableDevices($user);
    }

    return $result->devices;
  }

  public function setActiveDevice(User $user, string $deviceId)
  {
    $data = array(
      "device_ids" => array($deviceId),
      "play" => true
    );

    $content = Requests::put("https://api.spotify.com/v1/me/player", $data, $user->spotifyAccessToken);
    $result = json_decode($content);

    if ($this->spotifyAuthService->isAccessTokenExpired($result)) {
      $user = $this->spotifyAuthService->refreshUserAccessToken($user);
      return $this->setActiveDevice($user, $deviceId);
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
}
