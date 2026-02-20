<?php $layout = 'admin'; $adminPage = 'servers'; $pageTitle = 'Server: ' . e($server['name']); ?>

<div class="flex-between mb-2">
    <h1 class="page-title"><?= e($server['name']) ?></h1>
    <div class="flex gap-1" style="flex-wrap:wrap;">
        <a href="/server/<?= $server['id'] ?>" target="_blank" class="btn btn-sm btn-secondary">
            <i class="fas fa-external-link-alt"></i> View Public
        </a>
        <a href="/admin/servers" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="grid-4 mb-3">
    <div class="admin-stat-card">
        <div class="admin-stat-icon <?= ($server['is_online'] ?? false) ? 'green' : 'red' ?>">
            <i class="fas fa-<?= ($server['is_online'] ?? false) ? 'wifi' : 'times-circle' ?>"></i>
        </div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= ($server['is_online'] ?? false) ? 'Online' : 'Offline' ?></div>
            <div class="stat-label">Status</div>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= (int)($server['players_online'] ?? 0) ?>/<?= (int)($server['players_max'] ?? 0) ?></div>
            <div class="stat-label">Players</div>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon purple">
            <i class="fas fa-thumbs-up"></i>
        </div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= (int)($server['vote_count'] ?? 0) ?></div>
            <div class="stat-label">Votes (30d) / <?= $totalVotes ?> total</div>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon gold">
            <i class="fas fa-rocket"></i>
        </div>
        <div class="admin-stat-body">
            <div class="stat-value"><?= (int)($server['boost_points'] ?? 0) ?></div>
            <div class="stat-label">Boost Points</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid-2 mb-3" style="max-width:700px;">
    <div class="card">
        <h3 class="section-title mb-1"><i class="fas fa-thumbs-up" style="color:var(--purple);"></i> Votes</h3>
        <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Total votes: <?= $totalVotes ?>. Last 30 days: <?= (int)($server['vote_count'] ?? 0) ?></p>
        <button class="btn btn-sm btn-danger"
                onclick="confirmAction('/admin/servers/<?= $server['id'] ?>/reset-votes', 'Reset Votes', 'This will delete ALL votes for this server. This cannot be undone.')">
            <i class="fas fa-trash"></i> Reset All Votes
        </button>
    </div>
    <div class="card">
        <h3 class="section-title mb-1"><i class="fas fa-rocket" style="color:var(--gold);"></i> Boosts</h3>
        <?php if (!empty($activeBoosts)): ?>
            <p class="text-muted" style="font-size:13px;margin-bottom:8px;">Active boosts:</p>
            <?php foreach ($activeBoosts as $boost): ?>
                <div style="font-size:13px;margin-bottom:4px;">
                    <span class="badge badge-gold"><?= e($boost['package_name'] ?? 'Unknown') ?></span>
                    <span class="text-muted">expires <?= time_ago($boost['expires_at']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted" style="font-size:13px;margin-bottom:8px;">No active boosts</p>
        <?php endif; ?>
        <button class="btn btn-sm btn-danger mt-1"
                onclick="confirmAction('/admin/servers/<?= $server['id'] ?>/reset-boosts', 'Reset Boosts', 'This will remove ALL boosts for this server. This cannot be undone.')">
            <i class="fas fa-trash"></i> Reset All Boosts
        </button>
    </div>
</div>

<!-- Edit Form -->
<div class="card" style="max-width:700px;">
    <h3 class="section-title mb-2">Edit Server</h3>
    <form method="POST" action="/admin/servers/<?= $server['id'] ?>/edit">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Server Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($server['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" style="min-height:120px;"><?= e($server['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Website</label>
            <input type="url" name="website" class="form-control" value="<?= e($server['website'] ?? '') ?>" placeholder="https://...">
        </div>

        <div class="form-group">
            <label>IP:Port (read-only)</label>
            <input type="text" class="form-control" value="<?= e($server['ip']) ?>:<?= $server['port'] ?>" disabled>
        </div>

        <?php if ($server['version'] ?? null): ?>
        <div class="form-group">
            <label>Version (auto-detected)</label>
            <input type="text" class="form-control" value="<?= e($server['version']) ?>" disabled>
        </div>
        <?php endif; ?>

        <div class="grid-2 mb-2">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_approved" value="1" <?= $server['is_approved'] ? 'checked' : '' ?>>
                    Approved
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?= $server['is_active'] ? 'checked' : '' ?>>
                    Active
                </label>
            </div>
        </div>

        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/servers" class="btn btn-secondary">Back to List</a>
        </div>
    </form>
</div>
