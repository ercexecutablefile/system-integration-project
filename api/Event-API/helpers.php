<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_PRETTY_PRINT);
    exit;
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    if (is_array($data)) {
        return $data;
    }

    return $_POST;
}

function require_api_key(): void
{
    $key = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? '');

    if (!hash_equals(API_KEY, $key)) {
        json_response([
            'status' => 'error',
            'message' => 'Missing or invalid API key.',
        ], 401);
    }
}

function apply_rate_limit(): void
{
    $storageDir = __DIR__ . '/storage';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0777, true);
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $bucket = date('YmdHi');
    $file = $storageDir . '/rate_limits.json';
    $limits = [];

    if (is_file($file)) {
        $decoded = json_decode((string) file_get_contents($file), true);
        $limits = is_array($decoded) ? $decoded : [];
    }

    foreach ($limits as $key => $_count) {
        if (!str_ends_with((string) $key, ':' . $bucket)) {
            unset($limits[$key]);
        }
    }

    $rateKey = $ip . ':' . $bucket;
    $limits[$rateKey] = ($limits[$rateKey] ?? 0) + 1;
    file_put_contents($file, json_encode($limits));

    if ($limits[$rateKey] > RATE_LIMIT_PER_MINUTE) {
        json_response([
            'status' => 'error',
            'message' => 'Rate limit exceeded. Please try again later.',
        ], 429);
    }
}

function positive_int(mixed $value, string $field): int
{
    $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($int === false) {
        json_response([
            'status' => 'error',
            'message' => "$field must be a positive integer.",
        ], 422);
    }

    return $int;
}

function required_string(array $data, string $field): string
{
    $value = trim((string) ($data[$field] ?? ''));
    if ($value === '') {
        json_response([
            'status' => 'error',
            'message' => "$field is required.",
        ], 422);
    }

    return $value;
}

function event_payload(array $data): array
{
    $date = required_string($data, 'event_date');
    $parsedDate = DateTime::createFromFormat('Y-m-d', $date);

    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
        json_response([
            'status' => 'error',
            'message' => 'event_date must use YYYY-MM-DD format.',
        ], 422);
    }

    return [
        'event_name' => required_string($data, 'event_name'),
        'event_desc' => required_string($data, 'event_desc'),
        'event_date' => $date,
        'max_capacity' => positive_int($data['max_capacity'] ?? null, 'max_capacity'),
        'created_by' => positive_int($data['created_by'] ?? 1, 'created_by'),
    ];
}

function attendee_payload(array $data): array
{
    $email = required_string($data, 'email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response([
            'status' => 'error',
            'message' => 'email must be a valid email address.',
        ], 422);
    }

    return [
        'name' => required_string($data, 'name'),
        'email' => $email,
        'event_id' => positive_int($data['event_id'] ?? null, 'event_id'),
    ];
}
