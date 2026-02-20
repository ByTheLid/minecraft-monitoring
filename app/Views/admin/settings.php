<?php $layout = 'admin'; $adminPage = 'settings'; $pageTitle = 'Settings'; ?>

<h1 class="page-title mb-2">Platform Settings</h1>

<div class="card" style="max-width:500px;">
    <form method="POST" action="/admin/settings">
        <?= csrf_field() ?>

        <h3 class="section-title mb-2"><i class="fas fa-trophy" style="color:var(--blue);"></i> Ranking Coefficients</h3>

        <?php
            $vals = [];
            foreach ($settings as $s) {
                $vals[$s['key']] = $s['value'];
            }
        ?>

        <div class="grid-2">
            <div class="form-group">
                <label>Kv (Votes)</label>
                <input type="number" step="0.1" name="rank_kv" class="form-control"
                       value="<?= e($vals['rank_kv'] ?? '1.0') ?>">
            </div>
            <div class="form-group">
                <label>Kb (Boost)</label>
                <input type="number" step="0.1" name="rank_kb" class="form-control"
                       value="<?= e($vals['rank_kb'] ?? '0.5') ?>">
            </div>
            <div class="form-group">
                <label>Ko (Online)</label>
                <input type="number" step="0.1" name="rank_ko" class="form-control"
                       value="<?= e($vals['rank_ko'] ?? '0.3') ?>">
            </div>
            <div class="form-group">
                <label>Ku (Uptime)</label>
                <input type="number" step="0.1" name="rank_ku" class="form-control"
                       value="<?= e($vals['rank_ku'] ?? '0.2') ?>">
            </div>
        </div>

        <h3 class="section-title mb-2 mt-2"><i class="fas fa-sliders-h" style="color:var(--gold);"></i> Limits</h3>

        <div class="form-group">
            <label>Max servers per user</label>
            <input type="number" name="max_servers_per_user" class="form-control"
                   value="<?= e($vals['max_servers_per_user'] ?? '5') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
