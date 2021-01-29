<?php

namespace SpotiSync\Services;

use SpotiSync\Models\User;
use SpotiSync\Repositories\UserRepository;
use SpotiSync\Utils\Requests;

class SpotifyAuthService
{
  private static $redirectUri = "https://agile114.science.uva.nl/api/authentication/callback.php";
  private static $clientId = null;
  private static $clientSecret = null;
  private static $scopes = "user-top-read user-modify-playback-state user-read-private user-read-email user-read-recently-played user-read-playback-position user-read-playback-state";

  private UserRepository $userRepository;

  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
    SpotifyAuthService::$clientId = $_ENV["SPOTIFY_CLIENT_ID"];
    SpotifyAuthService::$clientSecret = $_ENV["SPOTIFY_CLIENT_SECRET"];
  }

  public function redirectAuthorization()
  {
    $url = "https://accounts.spotify.com/authorize?"
      . "client_id=" . SpotifyAuthService::$clientId
      . "&response_type=code"
      . "&redirect_uri=" . urlencode(SpotifyAuthService::$redirectUri)
      . "&scope=" . SpotifyAuthService::$scopes;

    header("Location: {$url}");
  }

  public function getTokenData($authorizationCode)
  {
    $url = "https://accounts.spotify.com/api/token";

    $data = array(
      "client_id" => SpotifyAuthService::$clientId,
      "client_secret" => SpotifyAuthService::$clientSecret,
      "grant_type" => "authorization_code",
      "code" => $authorizationCode,
      "redirect_uri" => SpotifyAuthService::$redirectUri
    );

    $result = Requests::post($url, $data);
    $result = json_decode($result);

    return $result;
  }

  public function refreshAccessToken($refreshToken)
  {
    $url = "https://accounts.spotify.com/api/token";

    $data = array(
      "client_id" => SpotifyAuthService::$clientId,
      "client_secret" => SpotifyAuthService::$clientSecret,
      "grant_type" => "refresh_token",
      "refresh_token" => $refreshToken
    );

    $result = Requests::post($url, $data);
    $result = json_decode($result);

    return $result->access_token;
  }

  public function refreshUserAccessToken(User $user)
  {
    $newAccessToken = $this->refreshAccessToken($user->spotifyRefreshToken);
    $this->userRepository->setSpotifyTokens($user->id, $newAccessToken, $user->spotifyRefreshToken);
    $user->spotifyAccessToken = $newAccessToken;

    return $user;
  }

  public function getAppAccessToken()
  {
    $data = array(
      "grant_type" => "client_credentials"
    );

    $token = base64_encode(SpotifyAuthService::$clientId . ":" . SpotifyAuthService::$clientSecret);

    $result = Requests::post("https://accounts.spotify.com/api/token", $data, $token, "Basic");
    $result = json_decode($result);

    return $result->access_token;
  }

  public function isAccessTokenExpired($result)
  {
    return isset($result->error) && $result->error->status === 401 && $result->error->message === "The access token expired";
  }
}
