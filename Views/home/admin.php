<?php
// Views/home/admin.php
// Admin dashboard
require __DIR__ . '/../partials/header.php';

start_session_once();
?>

<h2>Welcome <?= e($_SESSION['user']['name'] ?? 'Admin') ?></h2>

<p>Admin Tools</p>
<ul>
    <li><a href="<?= base_url() ?>/church/requests">Review Church Requests</a></li>
    <li><a href="<?= base_url() ?>/church">Manage Churches</a></li>
    <li><a href="<?= base_url() ?>/admin/manage-users">Manage Users</a></li>
    <li><a href="<?= base_url() ?>/events">Manage Events</a></li>
</ul>