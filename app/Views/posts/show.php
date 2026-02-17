<?php $layout = 'main'; $currentPage = 'posts'; $pageTitle = e($post['title']); ?>

<div class="container" style="max-width:800px;">
    <article>
        <div class="mb-2">
            <a href="/posts" class="text-muted"><i class="fas fa-arrow-left"></i> Back to News</a>
        </div>

        <span class="tag mb-1"><?= e($post['category']) ?></span>
        <h1 class="page-title" style="margin-bottom:8px;"><?= e($post['title']) ?></h1>
        <div class="text-muted mb-3" style="font-size:13px;">
            by <?= e($post['author_name'] ?? 'Admin') ?>
            · <?= date('M j, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?>
        </div>

        <?php if ($post['cover_image']): ?>
            <img src="<?= e($post['cover_image']) ?>" alt="" style="border-radius:var(--radius-lg); margin-bottom:24px; width:100%;">
        <?php endif; ?>

        <div class="card" style="line-height:1.8; font-size:15px;">
            <?= nl2br(e($post['content'])) ?>
        </div>
    </article>
</div>
