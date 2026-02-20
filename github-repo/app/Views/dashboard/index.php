<?php $layout = 'main'; $currentPage = 'dashboard'; $pageTitle = 'Dashboard'; ?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">My Dashboard</h1>
        <a href="/dashboard/add" class="btn btn-primary">+ Add Server</a>
    </div>

    <?php if ($flash = flash('success')): ?>
        <div class="alert alert-success"><?= e($flash) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-value"><?= count($servers) ?></div>
            <div class="stat-label">My Servers</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= array_sum(array_column($servers, 'vote_count')) ?></div>
            <div class="stat-label">Total Votes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= array_sum(array_column($servers, 'players_online')) ?></div>
            <div class="stat-label">Total Players</div>
        </div>
    </div>

    <!-- Server List -->
    <h2 class="section-title mb-2">My Servers</h2>

    <?php if (empty($servers)): ?>
        <div class="card text-center" style="padding:40px;">
            <p class="text-muted mb-2">You haven't added any servers yet.</p>
            <a href="/dashboard/add" class="btn btn-primary">Add Your First Server</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Server</th>
                        <th>Status</th>
                        <th>Players</th>
                        <th>Votes</th>
                        <th>Rating</th>
                        <th>Approved</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servers as $server): ?>
                        <tr>
                            <td>
                                <strong><?= e($server['name']) ?></strong><br>
                                <small class="text-muted"><?= e($server['ip']) ?>:<?= $server['port'] ?></small>
                            </td>
                            <td>
                                <?php if ($server['is_online'] ?? false): ?>
                                    <span class="status-badge status-online"><span class="status-dot"></span> Online</span>
                                <?php else: ?>
                                    <span class="status-badge status-offline"><span class="status-dot"></span> Offline</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)($server['players_online'] ?? 0) ?>/<?= (int)($server['players_max'] ?? 0) ?></td>
                            <td><?= (int)($server['vote_count'] ?? 0) ?></td>
                            <td class="text-gold"><?= round((float)($server['rank_score'] ?? 0), 1) ?></td>
                            <td>
                                <?= $server['is_approved'] ? '<span class="text-green">Yes</span>' : '<span class="text-muted">Pending</span>' ?>
                            </td>
                            <td>
                                <a href="/dashboard/edit/<?= $server['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="/dashboard/delete/<?= $server['id'] ?>" style="display:inline;"
                                      onsubmit="return confirm('Are you sure?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
