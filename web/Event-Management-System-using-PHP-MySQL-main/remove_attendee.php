<?php
require 'api_client.php';

if (isset($_POST['attendee_id']) && isset($_POST['event_id'])) {
    $attendee_id = $_POST['attendee_id'];
    $response = event_api_request('DELETE', '/attendees/' . urlencode((string) $attendee_id));
    echo $response['message'] ?? "Attendee removed successfully!";
}
?>
