<?php $layout = 'admin'; $adminPage = 'dashboard'; $pageTitle = 'Admin Dashboard'; ?>

<div class="grid-4 mb-3">
    <div class="card">
        <div class="text-muted" style="font-size:12px; text-transform:uppercase;">Total Servers</div>
        <div style="font-size:24px; font-weight:bold;"><?= $stats['servers'] ?></div>
    </div>
    <div class="card">
        <div class="text-muted" style="font-size:12px; text-transform:uppercase;">Total Users</div>
        <div style="font-size:24px; font-weight:bold;"><?= $stats['users'] ?></div>
    </div>
    <div class="card">
        <div class="text-muted" style="font-size:12px; text-transform:uppercase;">Total Votes</div>
        <div style="font-size:24px; font-weight:bold;"><?= $stats['votes'] ?></div>
    </div>
    <div class="card">
        <div class="text-muted" style="font-size:12px; text-transform:uppercase;">Active Boosts</div>
        <div style="font-size:24px; font-weight:bold; color:var(--accent-gold);"><?= $stats['boosts_active'] ?></div>
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
