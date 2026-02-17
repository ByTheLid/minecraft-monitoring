<?php $layout = 'main'; $currentPage = 'servers'; $pageTitle = 'Server List'; ?>

<div class="container">
    <h1 class="page-title mb-2">Server List</h1>

    <!-- Filters -->
    <form class="filters-bar" method="GET" action="/servers">
        <input type="text" name="search" class="form-control search-input" placeholder="Search servers..."
               value="<?= e($filters['search'] ?? '') ?>">
        <select name="sort" class="form-control">
            <option value="rank" <?= ($filters['sort'] ?? '') === 'rank' ? 'selected' : '' ?>>By Rating</option>
            <option value="players" <?= ($filters['sort'] ?? '') === 'players' ? 'selected' : '' ?>>By Players</option>
            <option value="votes" <?= ($filters['sort'] ?? '') === 'votes' ? 'selected' : '' ?>>By Votes</option>
            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
        </select>
        <select name="status" class="form-control">
            <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>All Status</option>
            <option value="online" <?= ($filters['status'] ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
            <option value="offline" <?= ($filters['status'] ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
        </select>
        <input type="text" name="version" class="form-control" placeholder="Version..." style="max-width:120px;"
               value="<?= e($filters['version'] ?? '') ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
    </form>

    <!-- Server List -->
    <div class="server-list">
        <?php if (empty($servers)): ?>
            <div class="card text-center" style="padding:40px;">
                <p class="text-muted">No servers found matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php $rank = (($meta['page'] - 1) * $meta['per_page']); ?>
            <?php foreach ($servers as $server): ?>
                <?php $rank++; ?>
                <div class="server-card">
                    <div class="server-icon">
                        <?php if (!empty($server['favicon_base64'])): ?>
                            <img src="<?= e($server['favicon_base64']) ?>" alt="">
                        <?php else: ?>
                            <i class="fas fa-cube"></i>
                        <?php endif; ?>
                    </div>
                    <div class="server-info">
                        <h3>
                            <span class="server-rank">#<?= $rank ?></span>
                            <a href="/server/<?= $server['id'] ?>"><?= e($server['name']) ?></a>
                        </h3>
                        <div class="server-meta">
                            <?php if ($server['is_online'] ?? false): ?>
                                <span class="status-badge status-online"><span class="status-dot"></span> Online</span>
                            <?php else: ?>
                                <span class="status-badge status-offline"><span class="status-dot"></span> Offline</span>
                            <?php endif; ?>
                            <span><i class="fas fa-users"></i> <?= (int)($server['players_online'] ?? 0) ?>/<?= (int)($server['players_max'] ?? 0) ?></span>
                            <?php if ($server['version'] ?? null): ?>
                                <span><i class="fas fa-code-branch"></i> <?= e($server['version']) ?></span>
                            <?php endif; ?>
                            <?php if ($server['ping_ms'] ?? null): ?>
                                <span><i class="fas fa-signal"></i> <?= $server['ping_ms'] ?>ms</span>
                            <?php endif; ?>
                            <span class="copy-ip" data-ip="<?= e($server['ip']) ?>:<?= $server['port'] ?>">
                                <i class="fas fa-copy"></i> <?= e($server['ip']) ?><?= $server['port'] != 25565 ? ':' . $server['port'] : '' ?>
                            </span>
                        </div>
                        <?php
                            $tags = json_decode($server['tags'] ?? '[]', true) ?: [];
                            if ($tags):
                        ?>
                            <div class="mt-1">
                                <?php foreach (array_slice($tags, 0, 5) as $tag): ?>
                                    <span class="tag"><?= e($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="server-actions">
                        <button class="btn btn-vote btn-sm" data-server-id="<?= $server['id'] ?>" onclick="voteServer(<?= $server['id'] ?>, this)">
                            <i class="fas fa-caret-up"></i> <?= (int)($server['vote_count'] ?? 0) ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($meta['total_pages'] ?? 1) > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $meta['total_pages']; $p++): ?>
                <?php
                    $params = $filters;
                    $params['page'] = $p;
                    $qs = http_build_query($params);
                ?>
                <?php if ($p == $meta['page']): ?>
                    <span class="active"><span><?= $p ?></span></span>
                <?php else: ?>
                    <a href="/servers?<?= $qs ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
