<?php
require '../config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$event_date = trim($data['event_date'] ?? '');
$location = trim($data['location'] ?? '');

if (!$title || !$event_date) {
    echo json_encode(["status" => "error", "message" => "Title and date are required"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param("ssss", $title, $description, $event_date, $location);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Event added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add event"]);
}
?>