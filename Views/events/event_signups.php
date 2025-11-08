<?php
// PURPOSE: Organizer/admin can see who volunteered for one event

?>
<h2>Volunteers for: <?= htmlspecialchars($event['title']) ?></h2>
<p>Church: <?= htmlspecialchars($event['church_name']) ?></p>
<p>Start: <?= htmlspecialchars($event['start_datetime']) ?></p>

<?php if (empty($volunteers)): ?>
    <p>No volunteers yet.</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Signed Up At</th>
        </tr>
        <?php foreach ($volunteers as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['name']) ?></td>
                <td><?= htmlspecialchars($v['email']) ?></td>
                <td><?= htmlspecialchars($v['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p><a href="<?= base_url() ?>/events">Back to My Events</a></p>