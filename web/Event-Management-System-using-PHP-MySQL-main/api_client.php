<?php
declare(strict_types=1);

const EVENT_API_BASE_URL = 'http://localhost/Project/Event-API/index.php';
const EVENT_API_KEY = 'student-event-api-key-2026';

function event_api_request(string $method, string $endpoint, array $payload = null): array
{
    $url = EVENT_API_BASE_URL . $endpoint;
    $ch = curl_init($url);
    $headers = [
        'Accept: application/json',
        'X-API-Key: ' . EVENT_API_KEY,
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['status' => 'error', 'message' => $error, 'http_code' => 0];
    }

    curl_close($ch);
    $decoded = json_decode($response, true);
    $decoded = is_array($decoded) ? $decoded : ['status' => 'error', 'message' => $response];
    $decoded['http_code'] = $statusCode;

    return $decoded;
}
