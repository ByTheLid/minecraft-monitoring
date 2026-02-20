<?php $layout = 'main'; $currentPage = 'posts'; $pageTitle = 'News'; ?>

<div class="container">
    <h1 class="page-title mb-2">News & Updates</h1>

    <?php if (empty($posts)): ?>
        <div class="card text-center" style="padding:40px;">
            <p class="text-muted">No posts yet. Stay tuned!</p>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <?php if ($post['cover_image']): ?>
                        <div class="post-image">
                            <img src="<?= e($post['cover_image']) ?>" alt="">
                        </div>
                    <?php endif; ?>
                    <div class="post-body">
                        <div class="post-category"><?= e($post['category']) ?></div>
                        <h3><a href="/post/<?= $post['id'] ?>"><?= e($post['title']) ?></a></h3>
                        <p class="post-excerpt"><?= e(mb_substr(strip_tags($post['content']), 0, 150)) ?>...</p>
                        <div class="post-date">
                            <?= time_ago($post['published_at'] ?? $post['created_at']) ?>
                            · by <?= e($post['author_name'] ?? 'Admin') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (($meta['total_pages'] ?? 1) > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $meta['total_pages']; $p++): ?>
                    <?php if ($p == $meta['page']): ?>
                        <span class="active"><span><?= $p ?></span></span>
                    <?php else: ?>
                        <a href="/posts?page=<?= $p ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
