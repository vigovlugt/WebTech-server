<?php

namespace SpotiSync\Utils;

class Requests
{
  public static function get($url, $token)
  {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      "Authorization: Bearer {$token}"
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
  }

  public static function post($url, $data, $token = null, $authType = "Bearer")
  {
    $curl = curl_init($url);

    if ($token !== null) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: $authType {$token}"
      ));
    }

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
  }



  public static function put($url, $data = null, $token = null)
  {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if (isset($token)) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer {$token}"
      ));
    }

    if (isset($data)) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
  }
}
