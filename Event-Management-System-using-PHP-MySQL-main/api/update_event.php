<?php
require '../config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$event_date = trim($data['event_date'] ?? '');
$location = trim($data['location'] ?? '');

if (!$id || !$title || !$event_date) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, location=?, updated_at=NOW() WHERE id=?");
$stmt->bind_param("ssssi", $title, $description, $event_date, $location, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Event updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update event"]);
}
?>