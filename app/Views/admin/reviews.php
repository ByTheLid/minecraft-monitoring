<?php $layout = 'admin'; $adminPage = 'reviews'; $pageTitle = 'Manage Reviews'; ?>

<div class="flex-between mb-2">
    <h1 style="font-size:16px;">Manage Reviews</h1>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Author</th>
                <th>Server</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reviews as $review): ?>
                <tr>
                    <td><?= $review['id'] ?></td>
                    <td><strong><?= e($review['username']) ?></strong></td>
                    <td><?= e($review['server_name']) ?></td>
                    <td>
                        <div class="text-gold" style="font-size: 14px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $review['rating'] ? '' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td>
                        <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px; color: var(--text-secondary);">
                            <?= e($review['comment']) ?>
                        </div>
                    </td>
                    <td class="text-muted" style="font-size: 12px;"><?= date('Y-m-d H:i', strtotime($review['created_at'])) ?></td>
                    <td>
                        <form method="POST" action="/admin/reviews/<?= $review['id'] ?>/delete" onsubmit="return confirm('Are you sure you want to delete this review?');" style="display:inline;">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" title="Delete Review"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($reviews)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:20px;">No reviews found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <div class="pagination mt-2">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
