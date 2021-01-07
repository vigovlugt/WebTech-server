<?php

require_once("../services/SpotifyAuthService.php");
require_once("../repositories/UserRepository.php");
require_once("../services/SpotifyService.php");

class AuthService
{
  private static $jwtSecret = "FJKSDLFJDSKL:jkl";

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

    $spotifyUser = $this->spotifyService->getUserInformation($tokenData->access_token);

    // Get SpotiSync user by spotify user id.
    $user = $this->userRepository->getBySpotifyId($spotifyUser->id);

    // Register new user if there is not already an user for this spotifyId.
    if (!$user) {
      $user = $this->createNewUser($spotifyUser);
    }

    // Save spotify tokens to database.
    $this->userRepository->setSpotifyTokens($user["id"], $tokenData->access_token, $tokenData->refresh_token);

    // Create SpotiSync auth token.
    $accessToken = $this->createAccessToken($user["id"], $user["name"]);

    $url = "https://agile114.science.uva.nl?accessToken=" . $accessToken;

    header("Location: {$url}");
  }

  public function createNewUser($spotifyUser)
  {
    $user = new User();
    $user->name = $spotifyUser->display_name;
    $user->spotifyId = $spotifyUser->id;

    return $this->userRepository->create($user);
  }

  // https://jwt.io/
  public function createAccessToken($userId, $userName)
  {
    $header = json_encode(array(
      "typ" => "jwt",
      "alg" => "sha256"
    ));

    $payload = json_encode(array(
      "sub" => $userId,
      "name" => $userName
    ));

    $bs64Header = $this->base64EncodeUrl($header);
    $bs64Payload = $this->base64EncodeUrl($payload);

    $signature = hash_hmac('sha256', $bs64Header . "." . $bs64Payload, AuthService::$jwtSecret, true);

    $bs64Signature = $this->base64EncodeUrl($signature);

    return $bs64Header . "." . $bs64Payload . "." . $bs64Signature;
  }

  public function verifyAccessToken($token)
  {
    // TODO: Verify access token on every request.
  }

  public static function base64EncodeUrl($string)
  {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
  }
}
