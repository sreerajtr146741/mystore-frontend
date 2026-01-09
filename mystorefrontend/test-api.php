<?php
$backend = "https://your-backend.onrender.com/api/test";

$response = file_get_contents($backend);

echo $response;
