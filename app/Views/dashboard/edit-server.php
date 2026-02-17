<?php $layout = 'main'; $currentPage = 'dashboard'; $pageTitle = 'Edit Server'; ?>

<div class="container" style="max-width:600px;">
    <h1 class="page-title mb-2">Edit Server</h1>

    <?php if ($error = flash('error')): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="/dashboard/edit/<?= $server['id'] ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Server Address</label>
                <input type="text" class="form-control" value="<?= e($server['ip']) ?>:<?= $server['port'] ?>" disabled>
                <small class="text-muted">Address cannot be changed after creation.</small>
            </div>

            <div class="form-group">
                <label for="name">Server Name *</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= e($server['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"><?= e($server['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="website">Website URL</label>
                <input type="url" id="website" name="website" class="form-control"
                       value="<?= e($server['website'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma separated)</label>
                <?php $tags = json_decode($server['tags'] ?? '[]', true) ?: []; ?>
                <input type="text" id="tags" name="tags" class="form-control"
                       value="<?= e(implode(', ', $tags)) ?>">
            </div>

            <div class="flex gap-1">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/dashboard" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
