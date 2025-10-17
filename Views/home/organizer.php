<h2>Welcome <?= e($_SESSION['user']['name']) ?></h2>
<p>Manage your church's events and coordinate volunteers.</p>
<ul>
    <li><a href="?route=events/create">Create New Event</a></li>
    <li><a href="?route=events/my">View My Events</a></li>
</ul>