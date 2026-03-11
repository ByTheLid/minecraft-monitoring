<?php 
$layout = 'main'; 
$pageTitle = e($seoPage['meta_title']); 
$metaDescription = e($seoPage['meta_description']);
?>

<?php if (!$isIndexed): ?>
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="<?= base_url('/servers') ?>">
<?php else: ?>
    <link rel="canonical" href="<?= base_url($seoPage['url_path']) ?>">
<?php endif; ?>

<div class="container">
    <div class="mb-4">
        <nav style="font-size: 0.9rem; margin-bottom: 16px;">
            <a href="/servers" style="color: var(--text-secondary); text-decoration: none;">Servers</a>
            <span class="text-muted mx-2">/</span>
            <span style="color: var(--text-primary); font-weight: 600;"><?= e(ucfirst($category)) ?>: <?= e($value) ?></span>
        </nav>

        <h1 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; margin-bottom: 8px;">
            <?= e($seoPage['h1']) ?>
        </h1>
        <p class="text-muted"><?= number_format($seoPage['server_count']) ?> servers found</p>
    </div>

    <?php if (empty($servers['data'])): ?>
        <div class="card text-center" style="padding: 60px 40px; border: 2px dashed rgba(148, 163, 184, 0.2);">
            <i class="fas fa-search mb-4 text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
            <h3 style="font-family: var(--font-heading); font-size: 1.5rem;">No Servers Found</h3>
            <p class="text-muted">No servers match this filter yet.</p>
            <a href="/servers" class="btn btn-primary mt-2">Browse All Servers</a>
        </div>
    <?php else: ?>
        <div class="grid-2">
            <?php foreach ($servers['data'] as $server): ?>
                <div class="card server-card" style="padding: 20px; border-radius: 14px; transition: transform 0.2s;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h3 style="font-size: 1.15rem; margin: 0;">
                            <a href="/server/<?= $server['id'] ?>" style="color: var(--text-primary); text-decoration: none; font-weight: 700;">
                                <?= e($server['name']) ?>
                            </a>
                            <?php if ($server['is_verified'] ?? false): ?>
                                <span class="text-green" title="Verified Server" style="font-size: 16px; margin-left: 5px;"><i class="fas fa-check-circle"></i></span>
                            <?php endif; ?>
                        </h3>
                        <?php if ($server['is_online']): ?>
                            <span style="color: #10b981; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Online
                            </span>
                        <?php else: ?>
                            <span style="color: #ef4444; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Offline
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="text-muted mb-3" style="font-size: 0.9rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= e(mb_substr($server['description'] ?? '', 0, 120)) ?>
                    </p>

                    <div class="d-flex justify-content-between" style="font-size: 0.88rem; color: var(--text-secondary);">
                        <span><i class="fas fa-users mr-1"></i> <?= number_format($server['players_online'] ?? 0) ?></span>
                        <span><i class="fas fa-thumbs-up mr-1"></i> <?= number_format($server['vote_count'] ?? 0) ?></span>
                        <span><i class="fas fa-code-branch mr-1"></i> <?= e($server['version'] ?? '?') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($servers['meta']['total_pages'] > 1): ?>
            <div class="d-flex justify-content-center mt-4" style="gap: 8px;">
                <?php for ($p = 1; $p <= $servers['meta']['total_pages']; $p++): ?>
                    <a href="?page=<?= $p ?>" class="btn btn-sm <?= $p === $servers['meta']['page'] ? 'btn-primary' : 'btn-secondary' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- SEO Text -->
    <?php if (!empty($seoText)): ?>
        <div class="card mt-4" style="padding: 30px; border-radius: 14px;">
            <div style="color: var(--text-secondary); line-height: 1.8; font-size: 0.95rem;">
                <?= nl2br(e($seoText)) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.server-card:hover { transform: translateY(-3px); border-color: var(--primary-color); }
</style>
