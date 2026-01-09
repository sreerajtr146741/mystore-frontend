<?php

$backend = "https://mystore-backend-gk8t.onrender.com/api/test"; // put your real backend URL here

// Use cURL for better reliability than file_get_contents
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Debug only
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === FALSE || $httpCode >= 400) {
    echo "❌ API connection FAILED (HTTP $httpCode)<br>";
    echo "cURL Error: $error<br>";
    echo "Possible reasons: CORS, Wrong URL, Backend not reachable, SSL issues<br>";
    echo "Raw Response: " . htmlspecialchars($response);
    exit;
}

echo "✔️ API connected successfully<br>";
echo "Backend Response:<br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>
