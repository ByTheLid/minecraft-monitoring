<?php 
$layout = 'main'; 
$pageTitle = 'Setup Two-Factor Authentication'; 
$backupCodes = flash('backup_codes') ? json_decode(flash('backup_codes'), true) : null;
?>

<div class="container" style="max-width: 600px;">
    <div class="mb-4">
        <a href="/dashboard/settings" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Settings
        </a>
    </div>

    <?php if ($backupCodes): ?>
        <!-- Backup Codes (show after enabling) -->
        <div class="card" style="border: 2px solid var(--accent-green); background: rgba(16,185,129,0.05); padding: 30px;">
            <h2 style="font-family: var(--font-heading); color: var(--accent-green); margin-bottom: 16px;">
                <i class="fas fa-check-circle mr-2"></i> 2FA Enabled!
            </h2>
            <p class="text-muted mb-3">Save these backup codes in a safe place. Each code can only be used once.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 20px;">
                <?php foreach ($backupCodes as $code): ?>
                    <code style="background: var(--bg-body); padding: 10px 16px; border-radius: 8px; font-size: 1rem; text-align: center; border: 1px solid var(--border-color); font-weight: 600;"><?= e($code) ?></code>
                <?php endforeach; ?>
            </div>

            <div style="background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3); border-radius: 10px; padding: 14px 18px; color: #fbbf24;">
                <i class="fas fa-exclamation-triangle mr-1"></i> These codes will NOT be shown again!
            </div>
        </div>
    <?php else: ?>
        <!-- Setup Form -->
        <div class="card" style="padding: 30px;">
            <h2 style="font-family: var(--font-heading); margin-bottom: 8px;">
                <i class="fas fa-shield-halved mr-2" style="color: var(--primary-color);"></i> Setup 2FA
            </h2>
            <p class="text-muted mb-4">Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>

            <!-- QR Code -->
            <div class="text-center mb-4" style="padding: 20px; background: #fff; border-radius: 12px; display: inline-block; margin: 0 auto;">
                <img src="<?= e($qrUrl) ?>" alt="QR Code" style="width: 200px; height: 200px;">
            </div>

            <!-- Manual Key -->
            <div class="mb-4">
                <label class="form-label text-muted" style="font-size: 0.85rem;">Or enter this key manually:</label>
                <div style="background: var(--bg-body); padding: 12px 18px; border-radius: 10px; font-family: monospace; font-size: 1.1rem; letter-spacing: 3px; text-align: center; border: 1px solid var(--border-color); word-break: break-all;">
                    <?= e($secret) ?>
                </div>
            </div>

            <!-- Verify Code -->
            <form action="/dashboard/2fa/enable" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Verification Code</label>
                    <input type="text" name="code" class="form-control" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required 
                           style="font-size: 1.5rem; text-align: center; letter-spacing: 8px; font-weight: 700;">
                    <small class="text-muted">Enter the 6-digit code from your authenticator app</small>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Enable 2FA</button>
            </form>
        </div>
    <?php endif; ?>
</div>
