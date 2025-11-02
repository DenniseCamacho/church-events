<?php
// View for organizer dashboard; shows event and church management links

start_session_once();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] !== 2) {
    http_response_code(403);
    exit('Access denied');
}
?>
<h2>Welcome <?= e($_SESSION['user']['name']) ?></h2>
<p>Manage your church's events and coordinate volunteers.</p>
<ul>
    <li><a href="<?= base_url() ?>/events/create">Create New Event</a></li>
    <li><a href="<?= base_url() ?>/events">View My Events</a></li>
    <li><a href="<?= base_url() ?>/church/request-form">Request a Church</a></li>
</ul>