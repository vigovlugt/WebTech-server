<?php

namespace SpotiSync\Services;

use SpotiSync\Models\User;
use SpotiSync\Repositories\UserRepository;

class AuthService
{
  private static $jwtSecret = "FJKSDLFJDSKL:jkl";

  private static $jwtVersion = 1;

  private $userRepository;
  private $spotifyAuthService;
  private $spotifyService;

  public function __construct(UserRepository $userRepository, SpotifyAuthService $spotifyAuthService, SpotifyService $spotifyService)
  {
    $this->userRepository = $userRepository;
    $this->spotifyAuthService = $spotifyAuthService;
    $this->spotifyService = $spotifyService;
  }

  public function startAuthorization()
  {
    return $this->spotifyAuthService->redirectAuthorization();
  }

  public function handleAutorizationCallback()
  {
    $tokenData = $this->spotifyAuthService->getTokenData($_GET["code"]);
    if (!isset($tokenData->access_token)) {
      echo "Access token not set";
      return;
    }

    $spotifyUser = $this->spotifyService->getUserInformationByToken($tokenData->access_token);

    // Get SpotiSync user by spotify user id.
    $user = $this->userRepository->getBySpotifyId($spotifyUser->id);

    // Register new user if there is not already an user for this spotifyId.
    if (!$user) {
      $user = $this->createNewUser($spotifyUser);
    }

    // Save spotify tokens to database.
    $this->userRepository->setSpotifyTokens($user->id, $tokenData->access_token, $tokenData->refresh_token);

    // Create SpotiSync auth token.
    $accessToken = $this->createAccessToken($user);

    $url = "https://agile114.science.uva.nl?accessToken=" . $accessToken;

    header("Location: {$url}");
  }

  public function createNewUser($spotifyUser)
  {
    $user = new User();
    $user->name = $spotifyUser->display_name;
    $user->spotifyId = $spotifyUser->id;
    trigger_error($spotifyUser->images[0]->url);
    $user->profileImageUrl = $spotifyUser->images[0]->url;

    return $this->userRepository->create($user);
  }

  // https://jwt.io/
  public static function createAccessToken(User $user)
  {
    $header = json_encode(array(
      "typ" => "jwt",
      "alg" => "sha256"
    ));

    $payload = json_encode(array(
      "sub" => $user->id,
      "name" => $user->name,
      "v" => AuthService::$jwtVersion
    ));

    $bs64Header = AuthService::base64UrlEncode($header);
    $bs64Payload = AuthService::base64UrlEncode($payload);

    $signature = hash_hmac('sha256', $bs64Header . "." . $bs64Payload, AuthService::$jwtSecret, true);

    $bs64Signature = AuthService::base64UrlEncode($signature);

    return $bs64Header . "." . $bs64Payload . "." . $bs64Signature;
  }

  public static function verifyAccessToken($token)
  {
    $parts = explode(".", $token);
    $bs64Header = $parts[0];
    $bs64Payload = $parts[1];
    $bs64Signature = $parts[2];

    $payload = AuthService::getJwtPayload($token);

    $serverSignature = hash_hmac('sha256', $bs64Header . "." . $bs64Payload, AuthService::$jwtSecret, true);
    $bs64ServerSignature = AuthService::base64UrlEncode($serverSignature);

    return $bs64Signature == $bs64ServerSignature && isset($payload->v) && $payload->v == AuthService::$jwtVersion;
  }

  public static function getJwtPayload($token)
  {
    $parts = explode(".", $token);
    $bs64Payload = $parts[1];

    $jsonPayload = AuthService::base64UrlDecode($bs64Payload);
    $payload = json_decode($jsonPayload);

    return $payload;
  }

  public static function getUserId(string $accessToken = null)
  {
    if ($accessToken == null) {
      $headers = apache_request_headers();
      if (!isset($headers["Authorization"])) {
        return null;
      }

      $authorizationHeader = $headers["Authorization"];
      $accessToken = explode(" ", $authorizationHeader)[1];
    }

    if (!AuthService::verifyAccessToken($accessToken)) {
      return null;
    }

    $payload = AuthService::getJwtPayload($accessToken);

    return $payload->sub;
  }

  public static function base64UrlEncode($string)
  {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
  }

  public static function base64UrlDecode($string)
  {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
  }
}
