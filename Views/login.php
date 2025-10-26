<?php
// Views/login.php — shows a login form with email and password.
start_session_once();
?>
<h2>Sign in</h2>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= base_url() ?>/login/submit">
    <?= csrf_field() ?>
    <label>Email
        <input type="email" name="email" required>
    </label>
    <br>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <button class="btn">Sign in</button>
</form>