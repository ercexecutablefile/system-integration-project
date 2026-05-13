<?php
require 'api_client.php';

if (isset($_POST['attendee_name']) && isset($_POST['attendee_email']) && isset($_POST['event_id'])) {
    $response = event_api_request('POST', '/attendees', [
        'name' => $_POST['attendee_name'],
        'email' => $_POST['attendee_email'],
        'event_id' => $_POST['event_id'],
    ]);

    echo $response['message'] ?? 'Attendee request completed.';
}
?>
