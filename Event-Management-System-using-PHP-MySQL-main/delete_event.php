<?php
require 'api_client.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) || isset($_GET['event_id'])) {
    $event_id = $_GET['id'] ?? $_GET['event_id'];
    $response = event_api_request('DELETE', '/events/' . urlencode((string) $event_id));
    $_SESSION['message'] = $response['message'] ?? 'Event delete request completed.';
    $_SESSION['message_type'] = (($response['status'] ?? '') === 'success') ? 'success' : 'danger';
}

header("Location: events.php");
exit;
?>
