<?php
// db.php — handles the database connection and returns a shared mysqli object.

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'church_events');

function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) return $conn;

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_errno) {
        http_response_code(500);
        die('Database connection failed');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
