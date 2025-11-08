<?php
// PURPOSE: Volunteer dashboard. Volunteers can view approved events and sign up.
// Volunteers do not create, edit, or delete events.

require __DIR__ . '/../partials/header.php';

start_session_once();
?>
<h2>Welcome <?= e($_SESSION['user']['name'] ?? 'Volunteer') ?></h2>

<p>Volunteer Actions</p>
<ul>
    <li><a href="<?= base_url() ?>/events/public">View Upcoming Events</a></li>
    <li><a href="<?= base_url() ?>/events/volunteer_signups">My Volunteer Sign-ups</a></li>
</ul>

<p>Only approved events are shown. Select an event and use "I want to volunteer" if available.</p>