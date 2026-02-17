<?php
$layout = 'admin';
$adminPage = 'posts';
$pageTitle = $post ? 'Edit Post' : 'New Post';
$action = $post ? "/admin/posts/edit/{$post['id']}" : '/admin/posts/create';
?>

<h1 style="font-size:16px;" class="mb-2"><?= $post ? 'Edit Post' : 'New Post' ?></h1>

<div class="card" style="max-width:700px;">
    <form method="POST" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" class="form-control"
                   value="<?= e($post['title'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" class="form-control">
                <option value="news" <?= ($post['category'] ?? '') === 'news' ? 'selected' : '' ?>>News</option>
                <option value="guide" <?= ($post['category'] ?? '') === 'guide' ? 'selected' : '' ?>>Guide</option>
                <option value="update" <?= ($post['category'] ?? '') === 'update' ? 'selected' : '' ?>>Update</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Content *</label>
            <textarea id="content" name="content" class="form-control" style="min-height:300px;" required><?= e($post['content'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_published" value="1"
                       <?= ($post['is_published'] ?? 0) ? 'checked' : '' ?>>
                Publish immediately
            </label>
        </div>

        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">
                <?= $post ? 'Save Changes' : 'Create Post' ?>
            </button>
            <a href="/admin/posts" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
