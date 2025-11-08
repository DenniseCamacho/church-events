<?php
// PURPOSE: Guest landing page. Guests can view public events but cannot volunteer.

require __DIR__ . '/../partials/header.php';
start_session_once();
?>
<h2>Welcome</h2>

<p>This platform lists church events and volunteer opportunities.</p>

<p>
    <a href="<?= base_url() ?>/login">Login</a> |
    <a href="<?= base_url() ?>/events/public">View Events</a> | 
    <a href="<?= base_url() ?>/register">Register</a>
</p>
<p>Guests can browse events only. To volunteer, please create an account and log in.</p>
