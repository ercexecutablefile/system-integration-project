<?php
declare(strict_types=1);

const EVENT_API_BASE_URL = 'http://localhost/Project/Event-API/index.php';
const EVENT_API_KEY = 'student-event-api-key-2026';

/**
 * Main API request handler
 */
function event_api_request(string $method, string $endpoint, ?array $payload = null): array
{
    // Ensure proper URL formatting
    $endpoint = '/' . ltrim($endpoint, '/');
    $url = EVENT_API_BASE_URL . $endpoint;

    $ch = curl_init();

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-API-Key: ' . EVENT_API_KEY
    ];

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ]);

    if (!empty($payload)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    // Handle cURL failure
    if ($response === false) {
        return [
            'status' => 'error',
            'message' => $curlError ?: 'cURL request failed',
            'http_code' => 0,
            'data' => null
        ];
    }

    // Decode JSON safely
    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'status' => 'error',
            'message' => 'Invalid JSON response from API',
            'raw_response' => $response,
            'http_code' => $httpCode,
            'data' => null
        ];
    }

    // Ensure consistent structure
    return [
        'status' => $decoded['status'] ?? 'error',
        'message' => $decoded['message'] ?? '',
        'data' => $decoded['data'] ?? [],
        'http_code' => $httpCode
    ];
}