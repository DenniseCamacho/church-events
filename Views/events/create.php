<?php
// View for displaying the event creation form for admin and organizer
?>
<h2>Create Event</h2>

<?php if (!empty($error)): ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="<?= base_url() ?>/events/store">
    <?= csrf_field() ?>

    <label>Title *</label><br>
    <input type="text" name="title" required maxlength="160"><br><br>

    <label>Church *</label><br>
    <select name="church_id" required>
        <option value="">Select church</option>
        <?php foreach ($churches as $ch): ?>
            <option value="<?= $ch['id'] ?>"><?= htmlspecialchars($ch['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <p>Church not listed? <a href="<?= base_url() ?>/church/request">Request a Church</a></p>
    <br>

    <label>Start Date/Time *</label><br>
    <input type="datetime-local" name="start_datetime" required><br><br>

    <label>End Date/Time</label><br>
    <input type="datetime-local" name="end_datetime"><br><br>

    <label>Different Location (optional)</label><br>
    <small>If the event is not at the church, enter the new address or place name.</small><br>
    <input type="text" name="location_override" maxlength="160" placeholder="Example: Community Park, 123 Oak St"><br><br>
    <button type="submit">Save Event</button>
</form>