<?php
// Form for creating a new church record
?>

<h2>Add Church</h2>

<?php if (!empty($error)): ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="<?= base_url() ?>/church/store">
    <?= csrf_field() ?>

    <label>Name</label><br>
    <input type="text" name="name" required maxlength="160"><br><br>

    <label>Address 1</label><br>
    <input type="text" name="address1" maxlength="160"><br><br>

    <label>Address 2</label><br>
    <input type="text" name="address2" maxlength="160"><br><br>

    <label>City</label><br>
    <input type="text" name="city" required maxlength="120"><br><br>

    <label>State</label><br>
    <input type="text" name="state" required maxlength="2"><br><br>

    <label>Postal Code</label><br>
    <input type="text" name="postal_code" maxlength="10"><br><br>

    <button type="submit">Create</button>
</form>

<a href="<?= base_url() ?>/church">Back to list</a>