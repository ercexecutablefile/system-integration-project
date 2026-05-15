<?php
session_start();
require 'api_client.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = event_api_request('POST', '/events', [
        'event_name' => $_POST['event_name'] ?? '',
        'event_desc' => $_POST['event_desc'] ?? '',
        'event_date' => $_POST['event_date'] ?? '',
        'max_capacity' => $_POST['max_capacity'] ?? '',
        'created_by' => $_SESSION['user_id'] ?? 1,
    ]);

    if (($response['status'] ?? '') === 'success') {
        $_SESSION['message'] = "Event added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = $response['message'] ?? "Failed to add event. Please try again.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: events.php");
    exit;
}
?>
