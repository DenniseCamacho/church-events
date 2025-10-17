<?php
// layout.php — shared HTML shell for all pages.
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/churchevents/css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Church Events | Find Service & Volunteer Opportunities</title>
    <meta name="description" content="Browse church events, services, and volunteer opportunities in your area.">
</head>

<body>
    <header><?php include __DIR__ . '/partials/header.php'; ?></header>
    <main class="container">
        <?php
        if (isset($viewFile) && file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<p>View not found.</p>";
        }
        ?>
    </main>
    <footer><?php include __DIR__ . '/partials/footer.php'; ?></footer>
</body>

</html>