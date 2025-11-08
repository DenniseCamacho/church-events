<?php
// PURPOSE: Show the logged-in volunteer their own sign-ups
?>
<h2>My Volunteer Sign-ups</h2>

<?php if (empty($signups)): ?>
    <p>You have not signed up for any events.</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>Event</th>
            <th>Church</th>
            <th>Start</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($signups as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['church_name']) ?></td>
                <td><?= htmlspecialchars($row['start_datetime']) ?></td>
                <td>
                    <form method="POST" action="<?= base_url() ?>/events/withdraw" onsubmit="return confirm('Withdraw from this event?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="event_id" value="<?= (int)$row['event_id'] ?>">
                        <button type="submit">Withdraw</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>