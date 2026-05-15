<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'api_client.php';

if (isset($_GET['id'])) {
    $attendee_id = $_GET['id'];
    event_api_request('DELETE', '/attendees/' . urlencode((string) $attendee_id));

    // Redirect back to the attendees page
    header("Location: attendees.php");
    exit;
} else {
    // Redirect if no ID is passed
    header("Location: attendees.php");
    exit;
}
?>
