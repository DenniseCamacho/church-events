<?php
// Public view for listing approved events for volunteers and guests
?>
<h2>Upcoming Events</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>Title</th>
        <th>Church</th>
        <th>Date/Time</th>
        <th>Location</th>
        <th>Volunteers</th>
        <th>Action</th>
    </tr>
    <?php foreach ($events as $ev): ?>
        <tr>
            <td><?= htmlspecialchars($ev['title']) ?></td>
            <td><?= htmlspecialchars($ev['church_name']) ?></td>
            <td><?= htmlspecialchars($ev['start_datetime']) ?></td>
            <td>
                <?php if (!empty($ev['location_override'])): ?>
                    <?= htmlspecialchars($ev['location_override']) ?>
                <?php else: ?>
                    <?= htmlspecialchars($ev['church_name']) ?>
                <?php endif; ?>
            </td>
            <td><?= (int)($ev['volunteer_count'] ?? 0) ?></td>
            <td>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role_id'] == 3): ?>
                    <form method="POST" action="<?= base_url() ?>/events/volunteer">
                        <?= csrf_field() ?>
                        <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
                        <button type="submit">I want to volunteer</button>
                    </form>
                <?php else: ?>
                    <small>Login as volunteer to sign up</small>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>