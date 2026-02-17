<?php $layout = 'admin'; $adminPage = 'posts'; $pageTitle = 'Posts'; ?>

<div class="flex-between mb-2">
    <h1 style="font-size:16px;">Manage Posts</h1>
    <a href="/admin/posts/create" class="btn btn-primary btn-sm">+ New Post</a>
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
                    <td><span class="tag"><?= e($post['category']) ?></span></td>
                    <td>
                        <?= $post['is_published']
                            ? '<span class="text-green">Published</span>'
                            : '<span class="text-muted">Draft</span>' ?>
                    </td>
                    <td class="text-muted"><?= time_ago($post['created_at']) ?></td>
                    <td>
                        <a href="/admin/posts/edit/<?= $post['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <form method="POST" action="/admin/posts/delete/<?= $post['id'] ?>" style="display:inline;"
                              onsubmit="return confirm('Delete this post?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
                <tr><td colspan="6" class="text-center text-muted" style="padding:20px;">No posts yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
