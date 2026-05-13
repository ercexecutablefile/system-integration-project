<?php
require 'api_client.php';

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $response = event_api_request('GET', '/attendees?event_id=' . urlencode((string) $event_id));
    echo json_encode($response['data'] ?? []);
}
?>
