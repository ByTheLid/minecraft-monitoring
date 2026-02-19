<?php $layout = 'admin'; $adminPage = 'analytics'; $pageTitle = 'Admin Analytics'; ?>

<div class="container">
    <div class="flex-between mb-3">
        <h1 class="page-title">Vote Analytics & RCON Logs</h1>
        <a href="/admin" class="btn btn-secondary">Back to Admin</a>
    </div>

    <!-- Toolbar -->
    <div class="filters-bar">
        <form action="" method="GET" class="flex gap-2" style="width:100%">
            <input type="text" name="search" class="form-control search-input" 
                   placeholder="Search username or server..." value="<?= e($search) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="card overflow-auto">
        <table style="width:100%; border-collapse:collapse; min-width:800px;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.1); text-align:left;">
                    <th style="padding:12px;">Date</th>
                    <th style="padding:12px;">User / IP</th>
                    <th style="padding:12px;">Server</th>
                    <th style="padding:12px;">Reward Status</th>
                    <th style="padding:12px;">Log</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($votes as $vote): ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:12px; color:var(--text-secondary);">
                            <?= $vote['voted_at'] ?>
                        </td>
                        <td style="padding:12px;">
                            <div style="font-weight:bold;"><?= e($vote['username'] ?: 'Unknown') ?></div>
                            <div style="font-size:11px; color:var(--text-secondary);"><?= e($vote['ip_address']) ?></div>
                        </td>
                        <td style="padding:12px;">
                            <a href="/server/<?= $vote['server_id'] ?>"><?= e($vote['server_name']) ?></a>
                        </td>
                        <td style="padding:12px;">
                            <?php if ($vote['reward_sent']): ?>
                                <span class="tag" style="color:var(--accent-green); border-color:var(--accent-green);">Success</span>
                            <?php else: ?>
                                <span class="tag" style="color:var(--text-secondary); border-color:rgba(255,255,255,0.1);">None / Failed</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px; max-width:300px;">
                            <?php if ($vote['reward_log']): ?>
                                <div style="font-size:10px; font-family:monospace; background:rgba(0,0,0,0.3); padding:4px; border-radius:3px; overflow-x:auto;">
                                    <?= e($vote['reward_log']) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($votes)): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding:20px;">No votes found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                       class="<?= $i == $page ? 'active' : '' ?>">
                        <span><?= $i ?></span>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    <?php endif; ?>
</div>
