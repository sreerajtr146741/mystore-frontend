<?php

$backend = "https://mystore-backend-gk8t.onrender.com/api/test"; // put your real backend URL here

$response = @file_get_contents($backend);

if ($response === FALSE) {
    echo "❌ API connection FAILED<br>";
    echo "Possible reasons: CORS, Wrong URL, Backend not reachable, SSL issues";
    exit;
}

echo "✔️ API connected successfully<br>";
echo "Backend Response:<br>";
echo "<pre>$response</pre>";
