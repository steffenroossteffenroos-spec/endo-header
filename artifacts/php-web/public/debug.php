<?php
    // API-Key holen (wie in deinem Hauptskript)
    $apiKey = getenv('ApiKey');

    // Endpoint für die Modell-Liste
    $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);

    $rawResponse = curl_exec($ch);

    if (curl_errno($ch)) {
        die("cURL Error: " . curl_error($ch));
    }

    curl_close($ch);

    // Ausgabe formatieren
    $data = json_decode($rawResponse, true);

    echo "<pre>";
    print_r($data);
    echo "</pre>";
?>