<?php
// View for listing pending events for admin approval only
?>
<h2>Pending Events</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>Title</th>
        <th>Church</th>
        <th>Start</th>
        <th>Created By</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($events as $ev): ?>
        <tr>
            <td><?= htmlspecialchars($ev['title']) ?></td>
            <td><?= htmlspecialchars($ev['church_name']) ?></td>
            <td><?= htmlspecialchars($ev['start_datetime']) ?></td>
            <td><?= htmlspecialchars($ev['creator_name'] ?? '') ?></td>
            <td>
                <form method="POST" action="<?= base_url() ?>/events/approve" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                    <button type="submit">Approve</button>
                </form>
                <form method="POST" action="<?= base_url() ?>/events/reject" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                    <button type="submit">Reject</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>