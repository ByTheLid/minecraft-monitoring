<?php $layout = 'admin'; $adminPage = 'dashboard'; $pageTitle = 'Dashboard'; ?>

<h1 class="page-title mb-3">Admin Dashboard</h1>

<div class="grid-4 mb-3">
    <div class="stat-card">
        <div class="stat-value"><?= $totalServers ?></div>
        <div class="stat-label">Total Servers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-gold"><?= $pendingServers ?></div>
        <div class="stat-label">Pending Review</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-blue"><?= $totalUsers ?></div>
        <div class="stat-label">Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-green"><?= $todayVotes ?></div>
        <div class="stat-label">Votes Today</div>
    </div>
</div>

<?php if ($pendingServers > 0): ?>
    <div class="alert alert-info">
        <strong><?= $pendingServers ?></strong> server(s) waiting for approval.
        <a href="/admin/servers?filter=pending">Review now <i class="fas fa-arrow-right"></i></a>
    </div>
<?php endif; ?>
