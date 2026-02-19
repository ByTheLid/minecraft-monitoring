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

            <!-- RCON Configuration -->
            <div class="card mt-2" style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.05);">
                <h3 style="font-size:14px; margin-bottom:15px; color:var(--accent-gold);">Reward Configuration (RCON)</h3>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label for="rcon_host">RCON Host (Optional)</label>
                        <input type="text" id="rcon_host" name="rcon_host" class="form-control"
                               value="<?= e(old('rcon_host')) ?>" placeholder="Leave empty to use Server IP">
                    </div>
                    <div class="form-group">
                        <label for="rcon_port">RCON Port</label>
                        <input type="number" id="rcon_port" name="rcon_port" class="form-control"
                               value="<?= e(old('rcon_port')) ?>" placeholder="25575">
                    </div>
                </div>

                <div class="form-group">
                    <label for="rcon_password">RCON Password</label>
                    <input type="password" id="rcon_password" name="rcon_password" class="form-control"
                           value="<?= e(old('rcon_password')) ?>">
                </div>

                <div class="form-group">
                    <label for="reward_command">Reward Command</label>
                    <input type="text" id="reward_command" name="reward_command" class="form-control"
                           value="<?= e(old('reward_command')) ?>" placeholder="give {player} diamond 1">
                    <small class="text-muted">Use <code>{player}</code> as a placeholder for the username.</small>
                </div>
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
