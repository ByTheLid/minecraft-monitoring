<?php $layout = 'main'; $pageTitle = e($user['username']) . "'s Profile"; ?>

<div class="container">
    <div class="profile-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title mb-0"><?= e($user['username']) ?></h1>
                <p class="text-muted">Member since <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
            </div>
            <div>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <span class="badge badge-gold">Admin</span>
                <?php else: ?>
                    <span class="badge badge-secondary">User</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="profile-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Left Column: Servers -->
        <div>
            <h2 class="section-title mb-2">Servers</h2>

            <?php if (empty($servers)): ?>
                <div class="card text-center" style="padding:40px;">
                    <p class="text-muted">This user has no active servers.</p>
                </div>
            <?php else: ?>
                <div class="grid-2">
                    <?php foreach ($servers as $server): ?>
                        <div class="card server-card">
                            <?php if (!empty($server['favicon_base64'])): ?>
                                <img src="<?= $server['favicon_base64'] ?>" class="server-icon-lg mb-2" alt="Icon">
                            <?php else: ?>
                                <div class="server-icon-placeholder-lg mb-2"><i class="fas fa-cube"></i></div>
                            <?php endif; ?>

                            <h3 class="server-title">
                                <a href="/server/<?= $server['id'] ?>"><?= e($server['name']) ?></a>
                            </h3>
                            
                            <div class="server-meta mb-2">
                                <?php if ($server['is_online']): ?>
                                    <span class="status-text online">Online</span>
                                <?php else: ?>
                                    <span class="status-text offline">Offline</span>
                                <?php endif; ?>
                            </div>

                            <div class="server-stats flex-between">
                                <span><i class="fas fa-users"></i> <?= $server['players_online'] ?></span>
                                <span><i class="fas fa-thumbs-up"></i> <?= $server['vote_count'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Recent Activity -->
        <div>
            <h2 class="section-title mb-2">Recent Votes</h2>
            <?php if (empty($votes)): ?>
                <div class="card p-2 text-center text-muted">No recent votes.</div>
            <?php else: ?>
                <div class="card p-0 overflow-hidden">
                    <ul class="list-group">
                        <?php foreach ($votes as $vote): ?>
                            <li class="list-group-item">
                                <small class="text-muted d-block"><?= time_ago($vote['voted_at']) ?></small>
                                Voted for <a href="/server/<?= $vote['server_id'] ?>"><strong><?= e($vote['server_name'] ?: 'Unknown Server') ?></strong></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
