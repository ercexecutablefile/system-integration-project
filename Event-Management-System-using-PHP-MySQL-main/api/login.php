<?php
require '../config.php';
header('Content-Type: application/json');

// Get POST data (form-data in Postman)
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validate input
if (!$email || !$password) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

// Find user by email
$stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    echo json_encode([
        "status" => "debug",
        "db_user" => $user,
        "input_password" => $password
    ]);
    exit;

} else {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit;
}