<?php

function httpPost($url, $data)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function httpGet($url, $token)
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

$code_verifier = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
$code_challenge_base64 = "KBZZeIjkoNOja4K4MxarMmgOuPAPjNO5BNaBJG0oWg4";

if(isset($_GET['code']))
{
    #echo $_GET['code'];

    $data = array(
        'client_id' => '3e30248a5c894061a7bb2e0fbcd8a185',
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => 'http://agile114.science.uva.nl',
        'code_verifier' => $code_verifier
    );
    $content = httpPost("https://accounts.spotify.com/api/token", $data);

    $result = json_decode($content);
    $acces_token = $result->access_token;

    $content = httpGet("https://api.spotify.com/v1/me", $acces_token);
    $result = json_decode($content);
    
    echo $result->display_name;

} else {
    $url = "https://accounts.spotify.com/authorize";
    $url = $url . "?client_id=3e30248a5c894061a7bb2e0fbcd8a185";
    $url = $url . "&response_type=code";
    $url = $url . "&code_challenge_method=S256";
    $url = $url . "&code_challenge={$code_challenge_base64}";
    $url = $url . "&redirect_uri=http%3A%2F%2Fagile114.science.uva.nl";
    $url = $url . "&scope=user-top-read";
    header("Location: {$url}");
}
?>