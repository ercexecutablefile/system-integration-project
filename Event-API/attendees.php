<?php
require_once 'helpers.php';

function list_attendees(): void
{
    $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

    if ($eventId > 0) {
        $stmt = db()->prepare(
            "SELECT attendees.*, events.event_name
             FROM attendees
             JOIN events ON attendees.event_id = events.id
             WHERE event_id = ?"
        );
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = db()->query(
            "SELECT attendees.*, events.event_name
             FROM attendees
             JOIN events ON attendees.event_id = events.id"
        );
    }

    json_response([
        'status' => 'success',
        'data' => $result->fetch_all(MYSQLI_ASSOC)
    ]);
}

function create_attendee(): void
{
    $data = request_json();

    $name = required_string($data, 'name');
    $email = required_string($data, 'email');
    $eventId = positive_int($data['event_id'] ?? null, 'event_id');

    $stmt = db()->prepare(
        "INSERT INTO attendees (name, email, event_id, created_at)
         VALUES (?, ?, ?, NOW())"
    );

    $stmt->bind_param('ssi', $name, $email, $eventId);
    $stmt->execute();

    json_response([
        'status' => 'success',
        'message' => 'Attendee added successfully.'
    ], 201);
}

function delete_attendee(int $id): void
{
    $stmt = db()->prepare("DELETE FROM attendees WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    json_response([
        'status' => 'success',
        'message' => 'Attendee deleted successfully.'
    ]);
}