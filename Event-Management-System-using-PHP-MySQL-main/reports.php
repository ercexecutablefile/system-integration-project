<?php
require 'api_client.php';

$response = event_api_request('GET', '/reports');

if (($response['status'] ?? '') !== 'success') {
    $report = [];
    $summary = [
        'total_events' => 0,
        'total_attendees' => 0,
        'total_capacity' => 0,
        'upcoming_events' => 0,
        'capacity_used_percent' => 0,
    ];
    $events = [];
    $logs = [];
} else {
    $report = $response['data'] ?? [];

    $summary = $report['summary'] ?? [
        'total_events' => 0,
        'total_attendees' => 0,
        'total_capacity' => 0,
        'upcoming_events' => 0,
        'capacity_used_percent' => 0,
    ];

    $events = $report['events'] ?? [];
    $logs = $report['logs'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('assets/images/reportsBg.jpg') no-repeat center center fixed;
            font-family: Arial, sans-serif;
            background-size: cover;
            min-height: 100vh;
        }
        .navbar { background-color: #2196f3; }
        .navbar-brand, .navbar-nav .nav-link { color: #fff !important; }
        .page-wrap {
            background: rgba(255, 255, 255, 0.94);
            min-height: calc(100vh - 56px);
            padding: 32px 0;
        }
        .metric {
            background: #fff;
            border-left: 5px solid #2196f3;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            min-height: 116px;
        }
        .metric .label {
            color: #5f6b76;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        .metric .value {
            color: #17212b;
            font-size: 2rem;
            font-weight: 700;
        }
        .panel {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .table-responsive {
            max-height: 390px;
        }
        .progress {
            min-width: 110px;
        }
        .log-badge {
            text-transform: capitalize;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Event Management</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php">Manage Events</a></li>
                <li class="nav-item"><a class="nav-link" href="attendees.php">Manage Attendees</a></li>
                <li class="nav-item"><a class="nav-link" href="logout_process.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="page-wrap">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="mb-1">Event Reports</h1>
                <p class="text-muted mb-0">Live event summary and website activity history from the shared API.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="generate_report.php?type=summary" class="btn btn-success">Export Summary CSV</a>
                <a href="generate_report.php?type=logs" class="btn btn-outline-primary">Export Logs CSV</a>
            </div>
        </div>

        <?php if (($response['status'] ?? '') !== 'success'): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($response['message'] ?? 'Unable to load report data from the API.') ?>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Total Events</div>
                    <div class="value"><?= (int) $summary['total_events'] ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Registered Attendees</div>
                    <div class="value"><?= (int) $summary['total_attendees'] ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Upcoming Events</div>
                    <div class="value"><?= (int) $summary['upcoming_events'] ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Capacity Used</div>
                    <div class="value"><?= htmlspecialchars((string) $summary['capacity_used_percent']) ?>%</div>
                </div>
            </div>
        </div>

        <div class="panel mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Event Performance</h4>
                <span class="text-muted">Updates when event or attendee records change</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Attendees</th>
                            <th>Capacity</th>
                            <th>Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <?php
                                $capacity = max(1, (int) $event['max_capacity']);
                                $attendees = (int) $event['attendees'];
                                $percent = min(100, round(($attendees / $capacity) * 100));
                            ?>
                            <tr>
                                <td><?= (int) $event['id'] ?></td>
                                <td><?= htmlspecialchars($event['event_name']) ?></td>
                                <td><?= htmlspecialchars($event['event_date']) ?></td>
                                <td><?= $attendees ?></td>
                                <td><?= $capacity ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?= $percent ?>%"><?= $percent ?>%</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Website Activity History</h4>
                <span class="text-muted">Latest <?= count($logs) ?> actions</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Action</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Actor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                                <td><span class="badge bg-primary log-badge"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td><?= htmlspecialchars($log['entity_type']) ?></td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= htmlspecialchars($log['actor']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
