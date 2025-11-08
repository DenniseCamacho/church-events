<?php
// View for listing all events for admin or organizer
?>
<h2>Events</h2>

<p><a href="<?= base_url() ?>/events/create">Create New Event</a></p>

<table border="1" cellpadding="5">
    <tr>
        <th>Title</th>
        <th>Church</th>
        <th>Start</th>
        <th>Status</th>
    </tr>
    <?php foreach ($events as $ev): ?>
        <tr>
            <td><?= htmlspecialchars($ev['title']) ?></td>
            <td><?= htmlspecialchars($ev['church_name']) ?></td>
            <td><?= htmlspecialchars($ev['start_datetime']) ?></td>
            <td><?= htmlspecialchars($ev['status']) ?></td>
            <?php if ($role === 2 || $role === 1): ?>
                <td>
                    <a href="<?= base_url() ?>/events/signups?id=<?= $ev['id'] ?>">View Volunteers</a>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>