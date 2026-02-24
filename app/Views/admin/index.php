<?php 
$layout = 'admin'; 
$adminPage = 'dashboard'; 
$pageTitle = 'Admin Dashboard'; 
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', '/js/admin-charts.js'];

$isDaemonRunning = ($daemonStatus['status'] !== 'stopped' && $daemonStatus['status'] !== 'dead (timeout)');
$daemonPillClass = $isDaemonRunning ? 'badge-success' : 'badge-danger';
$daemonStatusText = ucfirst($daemonStatus['status']);
?>

<div class="card mb-4" style="border-left: 4px solid <?= $isDaemonRunning ? 'var(--accent-green)' : 'var(--accent-red)' ?>;">
    <div class="flex-between align-center">
        <div>
            <h3 class="m-0 mb-1"><i class="fas fa-microchip"></i> Ping Daemon Status</h3>
            <div class="text-muted text-sm">
                State: <span class="badge <?= $daemonPillClass ?>"><?= e($daemonStatusText) ?></span> | 
                Last Active: <strong><?= e($daemonStatus['last_update'] ?? 'Never') ?></strong> | 
                Servers Processed: <strong><?= e($daemonStatus['servers_pinged'] ?? 0) ?></strong>
            </div>
        </div>
        <form method="POST" action="/admin/daemon" class="m-0">
            <?= csrf_field() ?>
            <?php if ($isDaemonRunning): ?>
                <input type="hidden" name="action" value="stop">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-stop"></i> Stop Daemon</button>
            <?php else: ?>
                <input type="hidden" name="action" value="start">
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-play"></i> Start Daemon</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="dashboard-stats mb-4">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['servers'] ?></div>
        <div class="stat-label">Total Servers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['users'] ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['votes'] ?></div>
        <div class="stat-label">Total Votes</div>
    </div>
    <div class="stat-card" style="border-color: rgba(245, 158, 11, 0.3);">
        <div class="stat-value text-gold"><?= $stats['boosts_active'] ?></div>
        <div class="stat-label">Active Boosts</div>
    </div>
</div>

<?php if (isset($pendingServers) && $pendingServers > 0): ?>
    <div class="alert alert-info mb-3">
        <i class="fas fa-exclamation-circle"></i>
        <strong><?= $pendingServers ?></strong> server(s) waiting for approval.
        <a href="/admin/servers?filter=pending">Review now <i class="fas fa-arrow-right"></i></a>
    </div>
<?php endif; ?>

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

<div class="grid-2">
    <!-- Recent Servers -->
    <div class="card">
        <h3 class="section-title mb-2">New Servers</h3>
        <table class="table table-sm">
            <tbody>
                <?php foreach ($recentServers as $s): ?>
                <tr>
                    <td><?= e($s['name']) ?></td>
                    <td class="text-right text-muted" style="font-size:12px;"><?= time_ago($s['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Servers -->
    <div class="card">
        <h3 class="section-title mb-2">Top Performers</h3>
        <table class="table table-sm">
            <tbody>
                <?php foreach ($topServers as $s): ?>
                <tr>
                    <td><?= e($s['name']) ?></td>
                    <td class="text-right"><strong><?= $s['vote_count'] ?></strong> votes</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- System Health -->
    <div class="card">
        <h3 class="section-title mb-2">System Health</h3>
        <div>
            <div class="flex-between mb-1">
                <span class="text-muted">PHP Version</span>
                <span><?= $health['php'] ?></span>
            </div>
            <div class="flex-between mb-1">
                <span class="text-muted">MySQL Version</span>
                <span><?= $health['db'] ?></span>
            </div>
            <div class="flex-between">
                <span class="text-muted">Web Server</span>
                <span><?= e($health['server']) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="chart-container mb-3" style="margin-top:20px;">
    <h3>Recent Activity</h3>
    <ul class="activity-feed" id="activityFeed">
        <li class="text-muted">Loading...</li>
    </ul>
</div>
