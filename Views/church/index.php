<?php
// Views/church/index.php
// Displays all churches in table form for admin/organizer

start_session_once();
$isAdmin = (($_SESSION['user']['role_id'] ?? 0) === 1);
?>

<h2>Churches</h2>

<?php if (!empty($success)): ?>
    <p><?= e($success) ?></p>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <p><?= e($error) ?></p>
<?php endif; ?>

<?php if ($isAdmin): ?>
    <a href="<?= base_url() ?>/church/create">Add New Church</a><br><br>
<?php else: ?>
    <a href="<?= base_url() ?>/church/request">Request a Church be Added</a><br><br>
<?php endif; ?>

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Name</th>
            <th>City</th>
            <th>State</th>
            <th>Postal</th>
            <th>Active</th>
            <?php if ($isAdmin): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($churches)): ?>
            <tr>
                <td colspan="<?= $isAdmin ? 6 : 5 ?>">No churches found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($churches as $church): ?>
                <tr>
                    <td><?= e($church->name) ?></td>
                    <td><?= e($church->city) ?></td>
                    <td><?= e($church->state) ?></td>
                    <td><?= e($church->postal_code) ?></td>
                    <td><?= $church->is_active ? 'Yes' : 'No' ?></td>

                    <?php if ($isAdmin): ?>
                        <td>
                            <a href="<?= base_url() ?>/church/edit?id=<?= (int)$church->id ?>">Edit</a>

                            <form method="POST"
                                action="<?= base_url() ?>/church/destroy"
                                style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$church->id ?>">
                                <button type="submit" onclick="return confirm('Delete this church?');">
                                    Delete
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>