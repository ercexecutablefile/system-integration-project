<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'api_client.php';

// Check if the event ID is provided in the URL
if (!isset($_GET['id'])) {
    echo "Event ID is missing.";
    exit;
}

$event_id = $_GET['id'];

$response = event_api_request('GET', '/events/' . urlencode((string) $event_id));
$event = $response['data'] ?? null;

if (!$event) {
    echo "Event not found.";
    exit;
}

// Update event if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_desc = $_POST['event_desc'];
    $event_date = $_POST['event_date'];
    $max_capacity = $_POST['max_capacity'];

    $response = event_api_request('PUT', '/events/' . urlencode((string) $event_id), [
        'event_name' => $event_name,
        'event_desc' => $event_desc,
        'event_date' => $event_date,
        'max_capacity' => $max_capacity,
        'created_by' => $_SESSION['user_id'] ?? 1,
    ]);

    if (($response['status'] ?? '') === 'success') {
        header("Location: events.php");
        exit;
    } else {
        echo $response['message'] ?? "Error updating event.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Edit Event - Event Management</title>
    <style>
        body {
            background:url('assets/images/edit-event.jpg') no-repeat center center fixed;
            font-family: 'Arial', sans-serif;
            background-size: cover;
            position: relative;
            overflow: hidden;
        }
        .navbar {
            background-color: #2196f3;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .navbar-nav .nav-link {
            color: #fff !important;
        }
        .container {
            margin-top: 40px;
        }
        .btn-custom {
            background-color: #2196f3;
            color: white;
            border: none;
        }
        .btn-custom:hover {
            background-color: #1976d2;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Event Management</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="attendees.php">Manage Attendees</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<div class="container">
    <h1 class="text-center mb-4">Edit Event</h1>

    <!-- Event Edit Form -->
    <div class="form-container">
        <form action="edit_event.php?id=<?= $event_id ?>" method="POST">
            <div class="mb-3">
                <label for="event_name" class="form-label">Event Name</label>
                <input type="text" class="form-control" id="event_name" name="event_name" value="<?= htmlspecialchars($event['event_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="event_desc" class="form-label">Description</label>
                <textarea class="form-control" id="event_desc" name="event_desc" rows="3" required><?= htmlspecialchars($event['event_desc']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" value="<?= $event['event_date'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="max_capacity" class="form-label">Max Capacity</label>
                <input type="number" class="form-control" id="max_capacity" name="max_capacity" value="<?= $event['max_capacity'] ?>" required>
            </div>
            <button type="submit" class="btn btn-custom">Update Event</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
