<?php
// Form for editing an existing church record
?>

<h2>Edit Church</h2>

<?php if (!empty($error)): ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/churchevents/church/update">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$church->id ?>">

    <label>Name</label><br>
    <input type="text" name="name" required maxlength="160" value="<?= htmlspecialchars($church->name) ?>"><br><br>

    <label>Address 1</label><br>
    <input type="text" name="address1" maxlength="160" value="<?= htmlspecialchars($church->address1) ?>"><br><br>

    <label>Address 2</label><br>
    <input type="text" name="address2" maxlength="160" value="<?= htmlspecialchars($church->address2) ?>"><br><br>

    <label>City</label><br>
    <input type="text" name="city" required maxlength="120" value="<?= htmlspecialchars($church->city) ?>"><br><br>

    <label>State</label><br>
    <input type="text" name="state" required maxlength="2" value="<?= htmlspecialchars($church->state) ?>"><br><br>

    <label>Postal Code</label><br>
    <input type="text" name="postal_code" maxlength="10" value="<?= htmlspecialchars($church->postal_code) ?>"><br><br>

    <label>Latitude</label><br>
    <input type="text" name="latitude" value="<?= htmlspecialchars($church->latitude) ?>"><br><br>

    <label>Longitude</label><br>
    <input type="text" name="longitude" value="<?= htmlspecialchars($church->longitude) ?>"><br><br>

    <label><input type="checkbox" name="is_active" <?= $church->is_active ? 'checked' : '' ?>> Active</label><br><br>

    <button type="submit">Save</button>
</form>

<a href="/churchevents/church">Back to list</a>
