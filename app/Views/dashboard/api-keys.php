<?php 
$layout = 'main'; 
$pageTitle = 'API Keys'; 
?>

<div class="container" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="section-title mb-1"><i class="fas fa-key text-muted mr-2"></i> API Keys</h1>
            <p class="text-muted mb-0">Manage your public API access keys</p>
        </div>
        <button onclick="document.getElementById('generateModal').classList.add('active')" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Generate Key
        </button>
    </div>

    <?php if (empty($keys)): ?>
        <div class="card text-center" style="padding: 60px 40px; border: 2px dashed rgba(148, 163, 184, 0.2); background: rgba(148, 163, 184, 0.02);">
            <i class="fas fa-key mb-4 text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: var(--text-primary); margin-bottom: 8px;">No API Keys</h3>
            <p class="text-muted mb-0">Generate your first key to access the Public API.</p>
        </div>
    <?php else: ?>
        <div class="card p-0 overflow-hidden" style="border-radius: 16px;">
            <table class="admin-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Rate Limit</th>
                        <th>Last Used</th>
                        <th>Status</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keys as $key): ?>
                        <tr>
                            <td style="font-weight: 700;"><?= e($key['name']) ?></td>
                            <td><code style="background: var(--bg-body); padding: 4px 10px; border-radius: 6px; font-size: 0.9rem;"><?= e($key['api_key_masked']) ?></code></td>
                            <td><?= $key['rate_limit'] ?> req/min</td>
                            <td class="text-muted"><?= $key['last_used_at'] ? time_ago($key['last_used_at']) : 'Never' ?></td>
                            <td>
                                <?php if ($key['is_active']): ?>
                                    <span style="color: #10b981; font-weight: 600;"><i class="fas fa-circle" style="font-size: 8px;"></i> Active</span>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-weight: 600;"><i class="fas fa-circle" style="font-size: 8px;"></i> Revoked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($key['is_active']): ?>
                                    <form action="/dashboard/api-keys/revoke" method="POST" style="display: inline;" onsubmit="return confirm('Revoke this key?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-ban"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (!empty($newKey)): ?>
        <div class="card mt-4" style="border: 2px solid var(--accent-green); background: rgba(16,185,129,0.05); padding: 24px;">
            <h4 style="color: var(--accent-green); margin-bottom: 12px;"><i class="fas fa-check-circle mr-2"></i> Your New API Key</h4>
            <p class="text-muted mb-3">Copy it now — it won't be shown again!</p>
            <div style="background: var(--bg-body); padding: 14px 20px; border-radius: 10px; font-family: monospace; font-size: 1.05rem; word-break: break-all; border: 1px solid var(--border-color);">
                <?= e($newKey) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- API Usage Info -->
    <div class="card mt-4" style="padding: 24px;">
        <h4 class="mb-3"><i class="fas fa-book mr-2 text-muted"></i> Quick Start</h4>
        <pre style="background: var(--bg-body); padding: 16px; border-radius: 10px; overflow-x: auto; margin: 0; border: 1px solid var(--border-color);"><code>curl -H "X-API-Key: YOUR_KEY" <?= base_url('/api/v1/servers') ?>

# Parameters: ?sort=rank|players|votes|newest
#             &search=keyword &status=online|offline
#             &version=1.20 &limit=20 &cursor=123</code></pre>
    </div>
</div>

<!-- Generate Modal -->
<div class="modal-backdrop" id="generateModal">
    <div class="modal" style="max-width: 420px;">
        <div class="modal-header">
            <h3>Generate API Key</h3>
            <button class="modal-close" onclick="document.getElementById('generateModal').classList.remove('active')">&times;</button>
        </div>
        <form action="/dashboard/api-keys/generate" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Key Name</label>
                <input type="text" name="name" class="form-control" placeholder="My App" required maxlength="100">
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-3">Generate</button>
        </form>
    </div>
</div>
