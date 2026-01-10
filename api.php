<?php

if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', 'https://mystore-backend-gk8t.onrender.com/api');
}

function callAPI($method, $endpoint, $data = false)
{
    $url = API_BASE_URL . $endpoint;

    $curl = curl_init();

    switch (strtoupper($method)) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;

        case "GET":
            if ($data) {
                $url = $url . '?' . http_build_query($data);
            }
            break;

        default:
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);

    if (curl_errno($curl)) {
        return [
            "status" => false,
            "message" => curl_error($curl)
        ];
    }

    curl_close($curl);

    return json_decode($result, true);
}
?>
