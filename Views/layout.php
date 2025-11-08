<?php

start_session_once();
$user = $_SESSION['user'] ?? null;
$role = $user['role_id'] ?? 0;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Church Events</title>
</head>

<body>

    <p>
        <a href="<?= base_url() ?>/home">Home</a>
        <?php if ($user): ?>
            · Hello, <?= e($user['name']) ?>
            · <a href="<?= base_url() ?>/logout">Logout</a>
        <?php else: ?>
            · <a href="<?= base_url() ?>/login">Login</a>
            · <a href="<?= base_url() ?>/register">Register</a>
        <?php endif; ?>
    </p>

    <?php if ($role === 1): ?>
        <p>
            <a href="<?= base_url() ?>/church">Churches</a> ·
            <a href="<?= base_url() ?>/church/requests">Church Requests</a> ·
            <a href="<?= base_url() ?>/events">Events</a> ·
            <a href="<?= base_url() ?>/events/pending">Pending Events</a> ·
            <a href="<?= base_url() ?>/events/public">Public Events</a>
        </p>
    <?php elseif ($role === 2): ?>
        <p>
            <a href="<?= base_url() ?>/events">My Events</a> ·
            <a href="<?= base_url() ?>/events/create">Create Event</a> ·
            <a href="<?= base_url() ?>/church/request">Request Church</a> ·
            <a href="<?= base_url() ?>/events/public">Public Events</a>
        </p>
    <?php elseif ($role === 3): ?>
        <p>
            <a href="<?= base_url() ?>/events/public">Upcoming Events</a> ·
            <a href="<?= base_url() ?>/events/volunteer_signups">My Sign-ups</a>
        </p>
    <?php else: ?>
        <p>
            <a href="<?= base_url() ?>/events/public">View Events</a>
        </p>
    <?php endif; ?>

    <?php
    // page content
    if (isset($viewFile) && file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "View not found.";
    }
    ?>

    <p>© <?= date('Y') ?> Church Events</p>
</body>

</html>