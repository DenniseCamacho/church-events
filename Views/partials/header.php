<?php
// header.php — top navigation, shared across all pages
start_session_once();
$u = current_user();
?>
<nav>
    <a href="/churchevents/">Home</a>
    <?php if ($u): ?>
        · <span>Hello, <?= e($u['handle']) ?></span>
        · <a href="/churchevents/?route=auth/logout">Logout</a>
    <?php else: ?>
        · <a href="/churchevents/?route=auth/login">Login</a>
        · <a href="/churchevents/?route=auth/register">Register</a>
    <?php endif; ?>
</nav>
<hr>