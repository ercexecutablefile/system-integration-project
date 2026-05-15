<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* =========================
   JSON RESPONSE
========================= */
function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_PRETTY_PRINT);
    exit;
}

/* =========================
   REQUEST JSON PARSER
========================= */
function request_json(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    return is_array($data) ? $data : $_POST;
}

/* =========================
   API KEY VALIDATION (FIXED FOR XAMPP)
========================= */
function require_api_key(): void
{
    $validKey = 'student-event-api-key-2026';

    // Normalize headers safely (XAMPP + Apache compatible)
    $headers = [];

    if (function_exists('getallheaders')) {
        $rawHeaders = getallheaders();

        if (is_array($rawHeaders)) {
            // normalize to lowercase keys
            $headers = array_change_key_case($rawHeaders, CASE_LOWER);
        }
    }

    // fallback for Apache/XAMPP
    $serverKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

    $clientKey =
        $headers['x-api-key']
        ?? $serverKey
        ?? '';

    if (!is_string($clientKey) || $clientKey !== $validKey) {
        json_response([
            'status' => 'error',
            'message' => 'Missing or invalid API key.'
        ], 401);
    }
}

/* =========================
   RATE LIMITING (FILE BASED)
========================= */
function apply_rate_limit(): void
{
    $storageDir = __DIR__ . '/storage';

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0777, true);
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $bucket = date('YmdHi'); // per minute window
    $file = $storageDir . '/rate_limits.json';

    $limits = [];

    if (file_exists($file)) {
        $decoded = json_decode((string) file_get_contents($file), true);
        if (is_array($decoded)) {
            $limits = $decoded;
        }
    }

    // clean old buckets (keep only current minute)
    foreach ($limits as $key => $count) {
        if (!str_ends_with((string)$key, ':' . $bucket)) {
            unset($limits[$key]);
        }
    }

    $rateKey = $ip . ':' . $bucket;

    $limits[$rateKey] = ($limits[$rateKey] ?? 0) + 1;

    file_put_contents($file, json_encode($limits));

    if ($limits[$rateKey] > RATE_LIMIT_PER_MINUTE) {
        json_response([
            'status' => 'error',
            'message' => 'Rate limit exceeded. Please try again later.'
        ], 429);
    }
}

/* =========================
   VALIDATION HELPERS
========================= */
function positive_int(mixed $value, string $field): int
{
    $validated = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if ($validated === false) {
        json_response([
            'status' => 'error',
            'message' => "$field must be a positive integer."
        ], 422);
    }

    return (int)$validated;
}

function required_string(array $data, string $field): string
{
    $value = trim((string)($data[$field] ?? ''));

    if ($value === '') {
        json_response([
            'status' => 'error',
            'message' => "$field is required."
        ], 422);
    }

    return $value;
}

/* =========================
   EVENT PAYLOAD VALIDATION
========================= */
function event_payload(array $data): array
{
    $date = required_string($data, 'event_date');

    $parsedDate = DateTime::createFromFormat('Y-m-d', $date);

    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
        json_response([
            'status' => 'error',
            'message' => 'event_date must use YYYY-MM-DD format.'
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

/* =========================
   ATTENDEE PAYLOAD VALIDATION
========================= */
function attendee_payload(array $data): array
{
    $email = required_string($data, 'email');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response([
            'status' => 'error',
            'message' => 'email must be a valid email address.'
        ], 422);
    }

    return [
        'name' => required_string($data, 'name'),
        'email' => $email,
        'event_id' => positive_int($data['event_id'] ?? null, 'event_id'),
    ];
}