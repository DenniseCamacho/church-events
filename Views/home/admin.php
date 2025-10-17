<h2>Welcome <?= e($_SESSION['user']['name']) ?></h2>
<p>Admin tools</p>
<ul>
    <li><a href="?route=admin/review-requests">Review Church Requests</a></li>
    <li><a href="?route=admin/manage-users">Manage Users</a></li>
</ul>