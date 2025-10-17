<?php
// home.php — decides which dashboard or welcome screen to show.
start_session_once();
$u = current_user();
$role = $u['role_id'] ?? 0;

// pick the view file based on role id
switch ($role) {
    case 1:
        $title = "Admin Dashboard";
        $viewFile = __DIR__ . "/home/admin.php";
        break;
    case 2:
        $title = "Organizer Dashboard";
        $viewFile = __DIR__ . "/home/organizer.php";
        break;
    case 3:
        $title = "Volunteer Dashboard";
        $viewFile = __DIR__ . "/home/volunteer.php";
        break;
    default:
        $title = "Welcome Guest";
        $viewFile = __DIR__ . "/home/guest.php";
}

// load the shared layout (it will include $viewFile)
require __DIR__ . '/layout.php';
