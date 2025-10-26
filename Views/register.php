<?php
// Views/register.php — create a new volunteer account; handle is auto-assigned.
start_session_once();
?>
<h2>Create account</h2>
<p><small>Your username will be assigned automatically to keep things clean and safe.</small></p>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= base_url() ?>/register/submit">
    <?= csrf_field() ?>
    <label>Full name
        <input name="name" required>
    </label>
    <br>
    <label>Email
        <input type="email" name="email" required>
    </label>
    <br>
    <label>Password
        <input type="password" name="password" minlength="8" required>
    </label>
    <br><label for="role">Registering as:</label>
    <select name="role" id="role" required>
        <option value="volunteer">Volunteer</option>
        <option value="organizer">Organizer</option>
    </select>

    <button class="btn">Create account</button>
</form>