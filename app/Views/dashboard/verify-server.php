<?php $layout = 'main'; $currentPage = 'dashboard'; $pageTitle = 'Verify Server'; ?>

<div class="container">
    <div class="mb-4">
        <a href="/dashboard" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h2 class="card-title" style="margin-bottom:15px;"><i class="fas fa-check-circle text-gold"></i> Verify Ownership</h2>
        <p class="text-muted mb-4">Complete verification for <strong><?= e($server['name']) ?></strong> to get the Verified Badge and a ranking boost.</p>

        <?php if ($flash = flash('error')): ?>
            <div class="alert alert-danger mb-4"><?= e($flash) ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <h4>Instructions</h4>
            <ol style="margin-top:10px; padding-left:20px; color:var(--text-muted); line-height: 1.6;">
                <li>Copy your unique verification token.</li>
                <li>Set this token anywhere in your server's MOTD (server.properties or proxy config).</li>
                <li>Restart your server or proxy to apply the new MOTD.</li>
                <li>Click the verification button below. We will ping your server and check if the token is present.</li>
                <li>Once verified, you can revert your MOTD back to normal.</li>
            </ol>
        </div>

        <div class="form-group mb-4">
            <label>Your Unique Token:</label>
            <input type="text" class="form-control text-center" value="<?= e($server['verify_token']) ?>" readonly style="font-family: var(--font-mono); font-size: 20px; font-weight: bold; color: var(--text-gold); background: var(--bg-main);">
        </div>

        <form method="POST" action="/dashboard/verify/<?= $server['id'] ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary w-100" style="padding: 12px; font-size: 16px;">
                <i class="fas fa-sync"></i> Check MOTD & Verify
            </button>
        </form>
    </div>
</div>
