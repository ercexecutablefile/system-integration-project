<?php
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'event_management';

const API_KEY = 'student-event-api-key-2026';
const RATE_LIMIT_PER_MINUTE = 100;

function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        json_response([
            'status' => 'error',
            'message' => 'Database connection failed',
        ], 500);
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
