<?php
require 'api_client.php';

$type = $_GET['type'] ?? 'summary';
$response = event_api_request('GET', '/reports');
$report = $response['data'] ?? [];

if (($response['status'] ?? '') !== 'success') {
    http_response_code(500);
    echo $response['message'] ?? 'Unable to generate report.';
    exit;
}

$filename = $type === 'logs' ? 'website_activity_logs.csv' : 'event_summary_report.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

if ($type === 'logs') {
    fputcsv($output, ['ID', 'Date/Time', 'Action', 'Entity Type', 'Entity ID', 'Description', 'Actor']);
    foreach ($report['logs'] ?? [] as $log) {
        fputcsv($output, [
            $log['id'],
            $log['created_at'],
            $log['action'],
            $log['entity_type'],
            $log['entity_id'],
            $log['description'],
            $log['actor'],
        ]);
    }
} else {
    fputcsv($output, ['ID', 'Event Name', 'Event Date', 'Max Capacity', 'Registered Attendees', 'Capacity Used Percent']);
    foreach ($report['events'] ?? [] as $event) {
        $capacity = max(1, (int) $event['max_capacity']);
        $attendees = (int) $event['attendees'];
        fputcsv($output, [
            $event['id'],
            $event['event_name'],
            $event['event_date'],
            $capacity,
            $attendees,
            round(($attendees / $capacity) * 100, 2),
        ]);
    }
}

fclose($output);
exit;
