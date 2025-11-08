<?php
// Form for organizer to request a new church to be added
// This writes to church_requests on submit
start_session_once();
?>
<h2>Request a Church</h2>

<?php if (!empty($success)): ?>
    <p><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($errors) && is_array($errors)): ?>
    <ul>
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST" action="<?= base_url() ?>/church/request/store">
    <?= csrf_field() ?>

    <label>Church Name *</label><br>
    <input type="text" name="church_name" required maxlength="160"
        value="<?= htmlspecialchars($old['church_name'] ?? '') ?>"><br><br>

    <label>Address 1</label><br>
    <input type="text" name="address1" maxlength="160"
        value="<?= htmlspecialchars($old['address1'] ?? '') ?>"><br><br>

    <label>Address 2</label><br>
    <input type="text" name="address2" maxlength="160"
        value="<?= htmlspecialchars($old['address2'] ?? '') ?>"><br><br>

    <label>City *</label><br>
    <input type="text" name="city" required maxlength="120"
        value="<?= htmlspecialchars($old['city'] ?? '') ?>"><br><br>

    <label>State (2-letter) *</label><br>
    <input type="text" name="state" required maxlength="2"
        value="<?= htmlspecialchars($old['state'] ?? '') ?>"><br><br>

    <label>Postal Code</label><br>
    <input type="text" name="postal_code" maxlength="10"
        value="<?= htmlspecialchars($old['postal_code'] ?? '') ?>"><br><br>

    <button type="submit">Submit Request</button>
</form>

<p><a href="<?= base_url() ?>/church">Back to Churches</a></p>