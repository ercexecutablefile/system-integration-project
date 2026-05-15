<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'api_client.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    $event_id = $_POST['event_id'] ?? 1;
    $response = event_api_request('PUT', '/attendees/' . urlencode((string) $id), [
        'name' => $name,
        'email' => $email,
        'event_id' => $event_id,
    ]);

    if (($response['status'] ?? '') === 'success') {
        $_SESSION['message'] = "Attendee updated successfully!";
    } else {
        $_SESSION['message'] = $response['message'] ?? "Failed to update attendee.";
    }

    header("Location: attendees.php");
    exit;
} else {
    header("Location: attendees.php");
    exit;
}
?>
