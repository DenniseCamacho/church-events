<?php
// header.php — top navigation shared across all pages
// Uses base_url() to generate correct links for both local and production environments

start_session_once();

// Optional helper function that returns the logged-in user array or null
$u = $_SESSION['user'] ?? null;



?>

<nav>
    <!-- Home link -->
    <a href="<?= base_url() ?>/">Home</a>

    <?php if ($u): ?>
        · <span>Hello, <?= e($u['handle']) ?></span>
        · <a href="<?= base_url() ?>/logout">Logout</a>
    <?php else: ?>
        · <a href="<?= base_url() ?>/login">Login</a>
        · <a href="<?= base_url() ?>/register">Register</a>
    <?php endif; ?>
</nav>

<hr>