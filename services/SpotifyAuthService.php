<?php

require_once("../utils/Requests.php");

require_once("../models/User.php");

class SpotifyAuthService
{
  private static $redirectUri = "https://agile114.science.uva.nl/api/authentication/callback.php";
  private static $clientId = "a396669e85dc48f78c0bd375588ffbde";
  private static $clientSecret = "f4b61155d8964c9781f1968e33500929";

  public function redirectAuthorization()
  {
    $url = "https://accounts.spotify.com/authorize?"
      . "client_id=" . SpotifyAuthService::$clientId
      . "&response_type=code"
      . "&redirect_uri=" . urlencode(SpotifyAuthService::$redirectUri)
      . "&scope=user-top-read";

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
}
