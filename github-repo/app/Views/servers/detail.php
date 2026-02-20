<?php
$layout = 'main';
$currentPage = 'servers';
$pageTitle = e($server['name']);
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', '/js/chart-init.js'];
?>

<div class="container">
    <!-- Header -->
    <div class="server-detail-header">
        <div class="server-icon-lg">
            <?php if (!empty($server['favicon_base64'])): ?>
                <img src="<?= e($server['favicon_base64']) ?>" alt="">
            <?php else: ?>
                <i class="fas fa-cube fa-2x"></i>
            <?php endif; ?>
        </div>
        <div class="server-detail-info">
            <h1><?= e($server['name']) ?></h1>
            <div class="flex gap-2" style="flex-wrap:wrap;">
                <?php if ($server['is_online'] ?? false): ?>
                    <span class="status-badge status-online"><span class="status-dot"></span> Online</span>
                <?php else: ?>
                    <span class="status-badge status-offline"><span class="status-dot"></span> Offline</span>
                <?php endif; ?>
                <span class="copy-ip" data-ip="<?= e($server['ip']) ?>:<?= $server['port'] ?>">
                    <i class="fas fa-copy"></i> <?= e($server['ip']) ?><?= $server['port'] != 25565 ? ':' . $server['port'] : '' ?>
                </span>
                <?php if ($server['version'] ?? null): ?>
                    <span class="tag"><?= e($server['version']) ?></span>
                <?php endif; ?>
                <span class="text-muted">by <?= e($server['owner_name'] ?? 'Unknown') ?></span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="server-detail-stats">
        <div class="stat-card">
            <div class="stat-value"><i class="fas fa-users"></i> <?= (int)($server['players_online'] ?? 0) ?>/<?= (int)($server['players_max'] ?? 0) ?></div>
            <div class="stat-label">Players</div>
            <?php
                $max = (int)($server['players_max'] ?? 1) ?: 1;
                $pct = round(((int)($server['players_online'] ?? 0) / $max) * 100);
            ?>
            <div class="progress-bar mt-1">
                <div class="progress-fill <?= $pct > 80 ? 'high' : '' ?>" style="width:<?= $pct ?>%"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><i class="fas fa-thumbs-up"></i> <?= (int)($server['vote_count'] ?? 0) ?></div>
            <div class="stat-label">Votes (30d)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><i class="fas fa-clock"></i> <?= round((float)($server['uptime_percent'] ?? 0), 1) ?>%</div>
            <div class="stat-label">Uptime (7d)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><i class="fas fa-signal"></i> <?= (int)($server['ping_ms'] ?? 0) ?>ms</div>
            <div class="stat-label">Ping</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-gold"><i class="fas fa-star"></i> <?= round((float)($server['rank_score'] ?? 0), 1) ?></div>
            <div class="stat-label">Rating</div>
        </div>
    </div>

    <!-- Vote -->
    <div class="flex-center mb-3">
        <button class="btn btn-vote btn-lg" data-server-id="<?= $server['id'] ?>" onclick="voteServer(<?= $server['id'] ?>, this)">
            <i class="fas fa-caret-up"></i> Vote for this server
        </button>
    </div>

    <!-- Charts -->
    <div class="tabs">
        <a class="tab active" data-period="24h" onclick="loadChart(<?= $server['id'] ?>, '24h', this)">24 Hours</a>
        <a class="tab" data-period="7d" onclick="loadChart(<?= $server['id'] ?>, '7d', this)">7 Days</a>
        <a class="tab" data-period="30d" onclick="loadChart(<?= $server['id'] ?>, '30d', this)">30 Days</a>
    </div>

    <div class="chart-container">
        <h3>Player Activity</h3>
        <div class="chart-wrapper">
            <canvas id="playersChart"></canvas>
        </div>
    </div>

    <!-- Description -->
    <?php if ($server['description']): ?>
        <div class="card mt-2">
            <h3 class="section-title mb-1">About</h3>
            <p style="color:var(--text-secondary); line-height:1.8;"><?= nl2br(e($server['description'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Tags -->
    <?php
        $tags = json_decode($server['tags'] ?? '[]', true) ?: [];
        if ($tags):
    ?>
        <div class="mt-2">
            <?php foreach ($tags as $tag): ?>
                <span class="tag"><?= e($tag) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($server['website']): ?>
        <div class="mt-2">
            <a href="<?= e($server['website']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                <i class="fas fa-globe"></i> Website
            </a>
        </div>
    <?php endif; ?>
</div>
