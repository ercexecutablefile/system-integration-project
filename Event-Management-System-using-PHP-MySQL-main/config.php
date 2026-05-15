<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_management";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]));
}
?>