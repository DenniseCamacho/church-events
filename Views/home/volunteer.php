<h2>Welcome <?= e($_SESSION['user']['name']) ?></h2>
<p>Thank you for volunteering! You can browse available events below.</p>
<ul>
    <li><a href="?route=events/list">View Events</a></li>
    <li><a href="?route=profile">My Profile</a></li>
</ul>