<?php
require_once 'helpers.php';

function list_events(): void
{
    $result = db()->query("SELECT * FROM events ORDER BY id DESC");
    $events = $result->fetch_all(MYSQLI_ASSOC);

    json_response([
        'status' => 'success',
        'data' => $events
    ]);
}

function create_event(): void
{
    $data = request_json();

    $name = required_string($data, 'event_name');
    $desc = required_string($data, 'event_desc');
    $date = required_string($data, 'event_date');
    $capacity = positive_int($data['max_capacity'] ?? null, 'max_capacity');
    $createdBy = positive_int($data['created_by'] ?? 1, 'created_by');

    $stmt = db()->prepare(
        "INSERT INTO events (event_name, event_desc, event_date, max_capacity, created_by, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
    );

    $stmt->bind_param('sssii', $name, $desc, $date, $capacity, $createdBy);
    $stmt->execute();

    json_response([
        'status' => 'success',
        'message' => 'Event created successfully.'
    ], 201);
}

function update_event(int $id): void
{
    $data = request_json();

    $name = required_string($data, 'event_name');
    $desc = required_string($data, 'event_desc');
    $date = required_string($data, 'event_date');
    $capacity = positive_int($data['max_capacity'] ?? null, 'max_capacity');

    $stmt = db()->prepare(
        "UPDATE events
         SET event_name=?, event_desc=?, event_date=?, max_capacity=?, updated_at=NOW()
         WHERE id=?"
    );

    $stmt->bind_param('sssii', $name, $desc, $date, $capacity, $id);
    $stmt->execute();

    json_response([
        'status' => 'success',
        'message' => 'Event updated successfully.'
    ]);
}

function delete_event(int $id): void
{
    $stmt = db()->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    json_response([
        'status' => 'success',
        'message' => 'Event deleted successfully.'
    ]);
}