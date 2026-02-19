<?php $layout = 'admin'; $adminPage = 'settings'; $pageTitle = 'Settings'; ?>

<?php $layout = 'admin'; $adminPage = 'settings'; $pageTitle = 'Settings'; ?>

<style>
    .settings-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .settings-tab { padding: 10px 20px; cursor: pointer; border-bottom: 2px solid transparent; color: var(--text-secondary); }
    .settings-tab.active { color: var(--accent-gold); border-bottom-color: var(--accent-gold); font-weight: bold; }
    .settings-content { display: none; }
    .settings-content.active { display: block; }
    .form-section { background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .form-text { font-size: 12px; color: var(--text-secondary); margin-top: 5px; }
</style>

<div class="container">
    <div class="flex-between mb-3">
        <h1 class="page-title">Platform Settings</h1>
        <button type="submit" form="settingsForm" class="btn btn-primary">Save Changes</button>
    </div>

    <form id="settingsForm" method="POST" action="/admin/settings">
        <?= csrf_field() ?>
        
        <?php 
            $vals = [];
            foreach ($settings as $s) {
                $vals[$s['key']] = $s['value'];
            }
        ?>

        <div class="settings-tabs">
            <div class="settings-tab active" data-tab="general">General</div>
            <div class="settings-tab" data-tab="economics">Economics & SEO</div>
            <div class="settings-tab" data-tab="socials">Socials & Contacts</div>
        </div>

        <!-- General Tab -->
        <div id="general" class="settings-content active">
            <div class="form-section">
                <h3 class="section-title mb-2">General Information</h3>
                <div class="form-group mb-2">
                    <label>Site Name</label>
                    <input type="text" name="site_name" class="form-control" value="<?= e($vals['site_name'] ?? 'MC Monitoring') ?>">
                </div>
                <div class="form-group mb-2">
                    <label>Site Description (Footer)</label>
                    <textarea name="site_description" class="form-control" rows="3"><?= e($vals['site_description'] ?? 'Best Minecraft servers monitoring.') ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="section-title mb-2">System Limits</h3>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Max servers per user</label>
                        <input type="number" name="max_servers_per_user" class="form-control" value="<?= e($vals['max_servers_per_user'] ?? '5') ?>">
                        <div class="form-text">Limit regular users to this many servers.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Economics Tab -->
        <div id="economics" class="settings-content">
            <div class="form-section">
                <h3 class="section-title mb-2">Ranking Algorithm</h3>
                <p class="mb-2 text-muted" style="font-size:13px;">Formula: <code>Rank = (Votes * Kv) + (Points * Kb) + (Online * Ko) + (Uptime * Ku)</code></p>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Kv (Votes Weight)</label>
                        <input type="number" step="0.1" name="rank_kv" class="form-control" value="<?= e($vals['rank_kv'] ?? '1.0') ?>">
                    </div>
                    <div class="form-group">
                        <label>Kb (Boost Weight)</label>
                        <input type="number" step="0.1" name="rank_kb" class="form-control" value="<?= e($vals['rank_kb'] ?? '0.5') ?>">
                    </div>
                    <div class="form-group">
                        <label>Ko (Online Weight)</label>
                        <input type="number" step="0.1" name="rank_ko" class="form-control" value="<?= e($vals['rank_ko'] ?? '0.3') ?>">
                    </div>
                    <div class="form-group">
                        <label>Ku (Uptime Weight)</label>
                        <input type="number" step="0.1" name="rank_ku" class="form-control" value="<?= e($vals['rank_ku'] ?? '0.2') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title mb-2">SEO Settings</h3>
                <div class="form-group">
                    <label>Meta Keywords</label>
                    <input type="text" name="seo_keywords" class="form-control" value="<?= e($vals['seo_keywords'] ?? 'minecraft, servers, monitoring, top') ?>">
                </div>
            </div>
        </div>

        <!-- Socials Tab -->
        <div id="socials" class="settings-content">
            <div class="form-section">
                <h3 class="section-title mb-2">Social Media Links</h3>
                <div class="form-group mb-2">
                    <label>Discord Invite URL</label>
                    <input type="url" name="social_discord" class="form-control" value="<?= e($vals['social_discord'] ?? '') ?>" placeholder="https://discord.gg/...">
                </div>
                <div class="form-group mb-2">
                    <label>VK Group URL</label>
                    <input type="url" name="social_vk" class="form-control" value="<?= e($vals['social_vk'] ?? '') ?>" placeholder="https://vk.com/...">
                </div>
                <div class="form-group mb-2">
                    <label>Telegram Channel</label>
                    <input type="url" name="social_telegram" class="form-control" value="<?= e($vals['social_telegram'] ?? '') ?>" placeholder="https://t.me/...">
                </div>
            </div>
        </div>

    </form>
</div>

<script>
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-content').forEach(c => c.classList.remove('active'));
            
            // Add to current
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
</script>
