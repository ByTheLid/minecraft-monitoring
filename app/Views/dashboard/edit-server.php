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

            <!-- RCON Configuration -->
            <div class="card mt-2" style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.05);">
                <h3 style="font-size:14px; margin-bottom:15px; color:var(--accent-gold);">Reward Configuration (RCON)</h3>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label for="rcon_host">RCON Host (Optional)</label>
                        <input type="text" id="rcon_host" name="rcon_host" class="form-control"
                               value="<?= e($server['rcon_host'] ?? '') ?>" placeholder="Leave empty to use Server IP">
                    </div>
                    <div class="form-group">
                        <label for="rcon_port">RCON Port</label>
                        <input type="number" id="rcon_port" name="rcon_port" class="form-control"
                               value="<?= e($server['rcon_port'] ?? '') ?>" placeholder="25575">
                    </div>
                </div>

                <div class="form-group">
                    <label for="rcon_password">RCON Password</label>
                    <input type="password" id="rcon_password" name="rcon_password" class="form-control"
                           value="<?= e($server['rcon_password'] ?? '') ?>" placeholder="Enter new password to change">
                </div>

                <div class="form-group">
                    <label for="reward_command">Reward Command</label>
                    <input type="text" id="reward_command" name="reward_command" class="form-control"
                           value="<?= e($server['reward_command'] ?? '') ?>" placeholder="give {player} diamond 1">
                    <small class="text-muted">Use <code>{player}</code> as a placeholder for the username.</small>
                </div>
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
