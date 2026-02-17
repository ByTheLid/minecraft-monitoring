<?php $layout = 'main'; $currentPage = 'home'; $pageTitle = 'Home'; ?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1>Minecraft Server <span>Monitor</span></h1>
        <p>Discover, track and vote for the best Minecraft servers. Real-time monitoring, rankings and statistics.</p>
        <div class="flex-center gap-2">
            <a href="/servers" class="btn btn-primary btn-lg btn-glow">Browse Servers</a>
            <a href="/register" class="btn btn-secondary btn-lg">Add Your Server</a>
        </div>
    </div>
</section>

<!-- Top Servers -->
<div class="container">
    <div class="flex-between mt-3 mb-2">
        <div class="flex gap-1" style="align-items:center;">
            <h2 class="section-title">Top Servers</h2>
            <button class="btn btn-sm btn-secondary" id="refreshBtn" onclick="refreshServers()" title="Refresh server data">
                <i class="fas fa-sync-alt"></i>
            </button>
            <span id="refreshStatus" class="refresh-status"></span>
        </div>
        <a href="/servers" class="btn btn-sm btn-secondary">View All <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="server-list" id="serverList">
        <?php if (empty($servers)): ?>
            <div class="card text-center" style="padding:40px;">
                <p class="text-muted">No servers yet. Be the first to <a href="/register">add yours</a>!</p>
            </div>
        <?php else: ?>
            <?php foreach ($servers as $i => $server): ?>
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
                            <span class="server-rank">#<?= $i + 1 ?></span>
                            <a href="/server/<?= $server['id'] ?>"><?= e($server['name']) ?></a>
                        </h3>
                        <div class="server-meta">
                            <?php if ($server['is_online'] ?? false): ?>
                                <span class="status-badge status-online">
                                    <span class="status-dot"></span> Online
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-offline">
                                    <span class="status-dot"></span> Offline
                                </span>
                            <?php endif; ?>
                            <span><i class="fas fa-users"></i> <?= (int)($server['players_online'] ?? 0) ?>/<?= (int)($server['players_max'] ?? 0) ?></span>
                            <?php if ($server['version'] ?? null): ?>
                                <span><i class="fas fa-code-branch"></i> <?= e($server['version']) ?></span>
                            <?php endif; ?>
                            <span class="copy-ip" data-ip="<?= e($server['ip']) ?>:<?= $server['port'] ?>">
                                <i class="fas fa-copy"></i> <?= e($server['ip']) ?><?= $server['port'] != 25565 ? ':' . $server['port'] : '' ?>
                            </span>
                        </div>
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

    <!-- Latest Posts -->
    <?php if (!empty($posts)): ?>
        <div class="flex-between mt-3 mb-2">
            <h2 class="section-title">Latest News</h2>
            <a href="/posts" class="btn btn-sm btn-secondary">All News <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="post-body">
                        <div class="post-category"><?= e($post['category']) ?></div>
                        <h3><a href="/post/<?= $post['id'] ?>"><?= e($post['title']) ?></a></h3>
                        <p class="post-excerpt"><?= e(mb_substr(strip_tags($post['content']), 0, 120)) ?>...</p>
                        <div class="post-date"><?= time_ago($post['published_at'] ?? $post['created_at']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    const COOLDOWN_AUTO = 60;
    const COOLDOWN_MANUAL = 30;

    function renderServerCard(s, rank) {
        const isOnline = s.is_online ? true : false;
        const statusClass = isOnline ? 'status-online' : 'status-offline';
        const statusText = isOnline ? 'Online' : 'Offline';
        const players = (s.players_online || 0) + '/' + (s.players_max || 0);
        const ip = s.ip + (s.port != 25565 ? ':' + s.port : '');
        const fullIp = s.ip + ':' + s.port;
        const icon = s.favicon_base64
            ? '<img src="' + escapeHtml(s.favicon_base64) + '" alt="">'
            : '<i class="fas fa-cube"></i>';
        const version = s.version ? '<span><i class="fas fa-code-branch"></i> ' + escapeHtml(s.version) + '</span>' : '';
        const votes = s.vote_count || 0;

        return '<div class="server-card">' +
            '<div class="server-icon">' + icon + '</div>' +
            '<div class="server-info">' +
                '<h3><span class="server-rank">#' + rank + '</span> <a href="/server/' + s.id + '">' + escapeHtml(s.name) + '</a></h3>' +
                '<div class="server-meta">' +
                    '<span class="status-badge ' + statusClass + '"><span class="status-dot"></span> ' + statusText + '</span>' +
                    '<span><i class="fas fa-users"></i> ' + players + '</span>' +
                    version +
                    '<span class="copy-ip" data-ip="' + escapeHtml(fullIp) + '"><i class="fas fa-copy"></i> ' + escapeHtml(ip) + '</span>' +
                '</div>' +
            '</div>' +
            '<div class="server-actions">' +
                '<button class="btn btn-vote btn-sm" data-server-id="' + s.id + '" onclick="voteServer(' + s.id + ', this)">' +
                    '<i class="fas fa-caret-up"></i> ' + votes +
                '</button>' +
            '</div>' +
        '</div>';
    }

    function updateServerList(servers) {
        const list = document.getElementById('serverList');
        if (!list || !servers || !servers.length) return;
        list.innerHTML = servers.map((s, i) => renderServerCard(s, i + 1)).join('');
    }

    let refreshTimer = null;
    let hideTimer = null;

    function setRefreshStatus(html, type) {
        const el = document.getElementById('refreshStatus');
        if (!el) return;
        el.innerHTML = html;
        el.className = 'refresh-status' + (type ? ' refresh-status--' + type : '');
    }

    function clearRefreshStatus(delay) {
        if (hideTimer) clearTimeout(hideTimer);
        if (delay) {
            hideTimer = setTimeout(() => setRefreshStatus('', ''), delay);
        } else {
            setRefreshStatus('', '');
        }
    }

    function setRefreshBtnState(loading) {
        const btn = document.getElementById('refreshBtn');
        if (!btn) return;
        btn.disabled = loading;
        btn.innerHTML = loading
            ? '<i class="fas fa-sync-alt fa-spin"></i>'
            : '<i class="fas fa-sync-alt"></i>';
    }

    function startCountdown(seconds) {
        if (refreshTimer) clearInterval(refreshTimer);
        let left = seconds;
        const render = (s) => '<i class="fas fa-satellite-dish"></i> Pinging servers... ~' + s + 's';
        setRefreshStatus(render(left), 'loading');
        refreshTimer = setInterval(() => {
            left--;
            setRefreshStatus(left > 0
                ? render(left)
                : '<i class="fas fa-spinner fa-spin"></i> Almost done...', 'loading');
        }, 1000);
    }

    function stopCountdown() {
        if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
    }

    async function doRefresh(force) {
        const url = '/api/servers/refresh' + (force ? '?force=1' : '');
        setRefreshBtnState(true);

        if (force) {
            startCountdown(15);
        } else {
            setRefreshStatus('<i class="fas fa-spinner fa-spin"></i> Updating...', 'loading');
        }

        try {
            const res = await api.get(url);

            if (res.success && res.data.refreshed && res.data.servers) {
                stopCountdown();
                updateServerList(res.data.servers);
                const msg = '<i class="fas fa-check-circle"></i> ' + res.data.online + '/' + res.data.total + ' online';
                setRefreshStatus(msg, 'done');
                clearRefreshStatus(5000);
                if (force) showToast(res.data.online + '/' + res.data.total + ' servers online', 'success');
            } else if (res.success && !res.data.refreshed) {
                stopCountdown();
                if (force && res.data.retry_after) {
                    const sec = res.data.retry_after;
                    setRefreshStatus('<i class="fas fa-clock"></i> Cooldown ' + sec + 's', 'loading');
                    clearRefreshStatus(3000);
                    showToast('Please wait ' + sec + 's before refreshing', 'info');
                } else {
                    clearRefreshStatus(0);
                }
            }
        } catch (e) {
            stopCountdown();
            // Only show error on manual refresh, silently fail on auto
            if (force) {
                setRefreshStatus('<i class="fas fa-exclamation-circle"></i> Failed', 'error');
                clearRefreshStatus(4000);
                showToast('Refresh failed', 'error');
            } else {
                clearRefreshStatus(0);
            }
        } finally {
            setRefreshBtnState(false);
        }
    }

    // Expose manual refresh
    window.refreshServers = function() {
        doRefresh(true);
    };

    // Auto-refresh on page load (silent)
    doRefresh(false);
})();
</script>
