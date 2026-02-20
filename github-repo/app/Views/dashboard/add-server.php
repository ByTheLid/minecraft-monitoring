<?php $layout = 'main'; $currentPage = 'dashboard'; $pageTitle = 'Add Server'; ?>

<div class="container" style="max-width:600px;">
    <h1 class="page-title mb-2">Add Server</h1>

    <?php if ($error = flash('error')): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="/dashboard/add">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Server Name *</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= e(old('name')) ?>" placeholder="My Awesome Server" required>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="ip">Server IP / Domain *</label>
                    <input type="text" id="ip" name="ip" class="form-control"
                           value="<?= e(old('ip')) ?>" placeholder="play.example.com" required>
                </div>
                <div class="form-group">
                    <label for="port">Port</label>
                    <input type="number" id="port" name="port" class="form-control"
                           value="<?= e(old('port', '25565')) ?>" min="1" max="65535">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"
                          placeholder="Tell players about your server..."><?= e(old('description')) ?></textarea>
            </div>

            <div class="form-group">
                <label for="website">Website URL</label>
                <input type="url" id="website" name="website" class="form-control"
                       value="<?= e(old('website')) ?>" placeholder="https://example.com">
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma separated)</label>
                <input type="text" id="tags" name="tags" class="form-control"
                       value="<?= e(old('tags')) ?>" placeholder="survival, pvp, minigames">
            </div>

            <div class="flex gap-1">
                <button type="submit" class="btn btn-primary">Add Server</button>
                <a href="/dashboard" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
