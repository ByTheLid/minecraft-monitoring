<?php $layout = 'admin'; $adminPage = 'posts'; $pageTitle = 'Posts'; ?>

<div class="flex-between mb-2">
    <h1 class="page-title">Manage Posts</h1>
    <a href="/admin/posts/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Post</a>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= $post['id'] ?></td>
                    <td><strong><?= e($post['title']) ?></strong></td>
                    <td><span class="badge badge-blue"><?= e($post['category']) ?></span></td>
                    <td>
                        <?= $post['is_published']
                            ? '<span class="badge badge-green"><i class="fas fa-check"></i> Published</span>'
                            : '<span class="badge badge-muted"><i class="fas fa-pen"></i> Draft</span>' ?>
                    </td>
                    <td class="text-muted"><?= time_ago($post['created_at']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <a href="/admin/posts/edit/<?= $post['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <button class="btn btn-sm btn-danger"
                                    onclick="confirmAction('/admin/posts/delete/<?= $post['id'] ?>', 'Delete Post', 'Are you sure you want to delete this post? This action cannot be undone.')">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
                <tr><td colspan="6" class="text-center text-muted" style="padding:20px;">No posts yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (($meta['total_pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $meta['total_pages']; $i++): ?>
            <?php if ($i === $meta['page']): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="/admin/posts?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
<?php endif; ?>
