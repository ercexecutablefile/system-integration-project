<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$route = trim(substr($path, strlen($scriptName)), '/');
$route = preg_replace('#^index\.php/?#', '', $route);
$segments = $route === '' ? [] : explode('/', trim($route, '/'));
$resource = $segments[0] ?? '';
$id = isset($segments[1]) ? positive_int($segments[1], 'id') : null;
$method = $_SERVER['REQUEST_METHOD'];

if ($resource === '' || $resource === 'health') {
    json_response([
        'status' => 'success',
        'message' => 'Student Event Integration API is running.',
        'docs' => '/Project/Event-API/docs.php',
    ]);
}

require_api_key();
apply_rate_limit();

match ($resource) {
    'events' => handle_events($method, $id),
    'attendees' => handle_attendees($method, $id),
    'reports' => handle_reports($method),
    default => json_response([
        'status' => 'error',
        'message' => 'Endpoint not found.',
    ], 404),
};

function handle_events(string $method, ?int $id): void
{
    $conn = db();

    if ($method === 'GET' && $id === null) {
        $search = '%' . trim((string) ($_GET['search'] ?? '')) . '%';
        $stmt = $conn->prepare(
            'SELECT e.*, COUNT(a.id) AS registered_guests
             FROM events e
             LEFT JOIN attendees a ON a.event_id = e.id
             WHERE e.event_name LIKE ?
             GROUP BY e.id
             ORDER BY e.event_date ASC, e.id ASC'
        );
        $stmt->bind_param('s', $search);
        $stmt->execute();
        json_response([
            'status' => 'success',
            'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
        ]);
    }

    if ($method === 'GET' && $id !== null) {
        $stmt = $conn->prepare(
            'SELECT e.*, COUNT(a.id) AS registered_guests
             FROM events e
             LEFT JOIN attendees a ON a.event_id = e.id
             WHERE e.id = ?
             GROUP BY e.id'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();

        if (!$event) {
            json_response(['status' => 'error', 'message' => 'Event not found.'], 404);
        }

        json_response(['status' => 'success', 'data' => $event]);
    }

    if ($method === 'POST') {
        $data = event_payload(request_json());
        $stmt = $conn->prepare(
            'INSERT INTO events (event_name, event_desc, event_date, max_capacity, current_attendees, created_by)
             VALUES (?, ?, ?, ?, 0, ?)'
        );
        $stmt->bind_param(
            'sssii',
            $data['event_name'],
            $data['event_desc'],
            $data['event_date'],
            $data['max_capacity'],
            $data['created_by']
        );
        $stmt->execute();
        json_response(['status' => 'success', 'message' => 'Event created.', 'id' => $conn->insert_id], 201);
    }

    if ($method === 'PUT' && $id !== null) {
        $data = event_payload(request_json());
        $stmt = $conn->prepare(
            'UPDATE events SET event_name = ?, event_desc = ?, event_date = ?, max_capacity = ? WHERE id = ?'
        );
        $stmt->bind_param(
            'sssii',
            $data['event_name'],
            $data['event_desc'],
            $data['event_date'],
            $data['max_capacity'],
            $id
        );
        $stmt->execute();
        json_response(['status' => 'success', 'message' => 'Event updated.']);
    }

    if ($method === 'DELETE' && $id !== null) {
        $stmt = $conn->prepare('DELETE FROM attendees WHERE event_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        json_response(['status' => 'success', 'message' => 'Event deleted.']);
    }

    json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
}

function handle_attendees(string $method, ?int $id): void
{
    $conn = db();

    if ($method === 'GET' && $id === null) {
        $eventId = isset($_GET['event_id']) ? positive_int($_GET['event_id'], 'event_id') : null;

        if ($eventId) {
            $stmt = $conn->prepare(
                'SELECT a.id, a.name, a.email, a.event_id, e.event_name
                 FROM attendees a
                 JOIN events e ON e.id = a.event_id
                 WHERE a.event_id = ?
                 ORDER BY a.id DESC'
            );
            $stmt->bind_param('i', $eventId);
        } else {
            $stmt = $conn->prepare(
                'SELECT a.id, a.name, a.email, a.event_id, e.event_name
                 FROM attendees a
                 JOIN events e ON e.id = a.event_id
                 ORDER BY a.id DESC'
            );
        }

        $stmt->execute();
        json_response(['status' => 'success', 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($method === 'POST') {
        $data = attendee_payload(request_json());
        ensure_event_capacity($data['event_id']);

        $stmt = $conn->prepare('INSERT INTO attendees (name, email, event_id) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $data['name'], $data['email'], $data['event_id']);
        $stmt->execute();
        json_response(['status' => 'success', 'message' => 'Attendee created.', 'id' => $conn->insert_id], 201);
    }

    if ($method === 'PUT' && $id !== null) {
        $data = attendee_payload(request_json());
        ensure_event_capacity($data['event_id'], $id);

        $stmt = $conn->prepare('UPDATE attendees SET name = ?, email = ?, event_id = ? WHERE id = ?');
        $stmt->bind_param('ssii', $data['name'], $data['email'], $data['event_id'], $id);
        $stmt->execute();
        json_response(['status' => 'success', 'message' => 'Attendee updated.']);
    }

    if ($method === 'DELETE' && $id !== null) {
        $stmt = $conn->prepare('DELETE FROM attendees WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        json_response(['status' => 'success', 'message' => 'Attendee deleted.']);
    }

    json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
}

function ensure_event_capacity(int $eventId, ?int $ignoreAttendeeId = null): void
{
    $conn = db();
    $stmt = $conn->prepare('SELECT max_capacity FROM events WHERE id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        json_response(['status' => 'error', 'message' => 'Event not found.'], 404);
    }

    if ($ignoreAttendeeId) {
        $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM attendees WHERE event_id = ? AND id <> ?');
        $stmt->bind_param('ii', $eventId, $ignoreAttendeeId);
    } else {
        $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM attendees WHERE event_id = ?');
        $stmt->bind_param('i', $eventId);
    }

    $stmt->execute();
    $total = (int) $stmt->get_result()->fetch_assoc()['total'];

    if ($total >= (int) $event['max_capacity']) {
        json_response(['status' => 'error', 'message' => 'Event capacity already reached.'], 409);
    }
}

function handle_reports(string $method): void
{
    if ($method !== 'GET') {
        json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
    }

    $result = db()->query(
        'SELECT e.id, e.event_name, e.event_date, e.max_capacity, COUNT(a.id) AS attendees
         FROM events e
         LEFT JOIN attendees a ON a.event_id = e.id
         GROUP BY e.id
         ORDER BY e.event_date ASC'
    );

    json_response(['status' => 'success', 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
}
