<?php
// PURPOSE: Organizer dashboard for managing own events and requesting churches
require __DIR__ . '/../partials/header.php';

start_session_once();
?>
<h2>Welcome <?= e($_SESSION['user']['name'] ?? 'Organizer') ?></h2>

<p>Organizer Tools</p>
<ul>
    <li><a href="<?= base_url() ?>/events/create">Create New Event</a></li>
    <li><a href="<?= base_url() ?>/events">View My Events</a></li>
    <li><a href="<?= base_url() ?>/events/public">All Events</a></li>
    <li><a href="<?= base_url() ?>/church/request">Request a Church</a></li>
</ul>
