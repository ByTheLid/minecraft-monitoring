<?php
$layout = 'admin';
$adminPage = 'dashboard';
$pageTitle = 'Dashboard';
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', '/js/admin-charts.js'];
?>

<div class="admin-welcome">
    <h1>Welcome back, <?= e(auth()['username']) ?></h1>
    <p><?= date('l, F j, Y') ?></p>
</div>

<div class="grid-4 mb-3">
    <div class="admin-stat-card">
        <div class="admin-stat-icon green"><i class="fas fa-server"></i></div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= $totalServers ?></div>
            <div class="stat-label">Total Servers (<?= $onlineServers ?> online)</div>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon gold"><i class="fas fa-clock"></i></div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= $pendingServers ?></div>
            <div class="stat-label">Pending Review</div>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-label">Users</div>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon purple"><i class="fas fa-thumbs-up"></i></div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= $todayVotes ?></div>
            <div class="stat-label">Votes Today</div>
        </div>
    </div>
</div>

<?php if ($pendingServers > 0): ?>
    <div class="alert alert-info mb-3">
        <i class="fas fa-exclamation-circle"></i>
        <strong><?= $pendingServers ?></strong> server(s) waiting for approval.
        <a href="/admin/servers?filter=pending">Review now <i class="fas fa-arrow-right"></i></a>
    </div>
<?php endif; ?>

<div class="quick-actions">
    <a href="/admin/servers?filter=pending" class="quick-action-card">
        <i class="fas fa-check-circle"></i> Review Pending Servers
    </a>
    <a href="/admin/posts/create" class="quick-action-card">
        <i class="fas fa-plus"></i> Create New Post
    </a>
    <a href="/admin/users" class="quick-action-card">
        <i class="fas fa-user-cog"></i> Manage Users
    </a>
    <a href="/admin/settings" class="quick-action-card">
        <i class="fas fa-cog"></i> Platform Settings
    </a>
</div>

<div class="grid-2 mb-3">
    <div class="chart-container">
        <h3>Registrations (7 days)</h3>
        <div class="chart-wrapper">
            <canvas id="registrationsChart"></canvas>
        </div>
    </div>
    <div class="chart-container">
        <h3>Votes (7 days)</h3>
        <div class="chart-wrapper">
            <canvas id="votesChart"></canvas>
        </div>
    </div>
</div>

<div class="chart-container">
    <h3>Recent Activity</h3>
    <ul class="activity-feed" id="activityFeed">
        <li class="text-muted">Loading...</li>
    </ul>
</div>
