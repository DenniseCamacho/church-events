<?php
// Views/church/requests_admin.php
// Admin review queue for church requests

start_session_once();
?>

<h2>Pending Church Requests</h2>

<?php if (empty($requests)): ?>
    <p>No pending or duplicate requests.</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Requested Name</th>
                <th>Location</th>
                <th>Requested By</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= e($r['church_name']) ?></td>
                    <td>
                        <?= e($r['address1']) ?>
                        <?php if (!empty($r['address2'])): ?>
                            , <?= e($r['address2']) ?>
                        <?php endif; ?>
                        <br>
                        <?= e($r['city']) ?>,
                        <?= e($r['state']) ?>
                        <?= e($r['postal_code']) ?>
                    </td>
                    <td>
                        <?= e($r['requester_name']) ?><br>
                        <?= e($r['requester_email']) ?>
                    </td>
                    <td><?= e($r['status']) ?></td>
                    <td><?= e($r['created_at']) ?></td>

                    <td>
                        <form method="POST"
                            action="<?= base_url() ?>/church/requests/approve"
                            style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                            <button type="submit">Approve</button>
                        </form>

                        <form method="POST"
                            action="<?= base_url() ?>/church/requests/reject"
                            style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                            <button type="submit">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<br>
<a href="<?= base_url() ?>/church">Back to Churches</a>