<?php $layout = 'dashboard'; $pageTitle = 'Boost Store - ' . e($server['name']); ?>

<div class="flex-between mb-2">
    <h1>Boost Store: <span class="text-gold"><?= e($server['name']) ?></span></h1>
    <a href="/dashboard" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="card mb-2" style="background: rgba(0,255,136,0.05); border-color: rgba(0,255,136,0.2);">
    <div class="flex-between" style="align-items:center;">
        <div>
            <h3 style="color:var(--text-primary); margin-bottom:4px;">Your Balance</h3>
            <p class="text-muted" style="font-size:13px;">Use coins to purchase boosts and stars for your server.</p>
        </div>
        <div style="font-family:var(--font-pixel); font-size:24px; color:var(--accent-green); text-shadow:var(--glow-green);">
            <i class="fas fa-coins"></i> <?= number_format($user['balance'], 2) ?>
        </div>
    </div>
</div>

<div class="grid-2 gap-2">
    <!-- Star Purchase -->
    <div class="card" style="border-color:var(--accent-gold); position:relative; overflow:hidden;">
        <div style="position:absolute; top:0; right:0; padding:10px; background:var(--accent-gold); color:#000; font-family:var(--font-pixel); font-size:10px; border-bottom-left-radius:8px;">
            UNIQUE
        </div>
        <h3 class="text-gold mb-1" style="font-size:18px;">
            <i class="fas fa-star"></i> Buy a Star
        </h3>
        <p class="text-muted mb-2" style="font-size:14px; min-height:45px;">
            Current Stars: <strong class="text-primary"><?= $currentStars ?>/3</strong><br>
            Make your server stand out! Each star visually upgrades your server on the ranking list. Next star costs twice as much.
        </p>

        <form method="POST" action="/dashboard/server/<?= $server['id'] ?>/boost/purchase">
            <?= csrf_field() ?>
            <input type="hidden" name="package_id" value="star">
            
            <?php if ($currentStars >= 3): ?>
                <button type="button" class="btn btn-block" disabled style="background:#444; color:#888; border-color:#444;">Maximum Stars Reached</button>
            <?php else: ?>
                <div class="flex-between mb-1" style="align-items:center;">
                    <span style="font-size:14px;">Price:</span>
                    <span class="text-gold" style="font-family:var(--font-pixel); font-size:16px;">
                        <?= number_format($starPrice, 2) ?> <i class="fas fa-coins" style="font-size:12px;"></i>
                    </span>
                </div>
                <?php if ($user['balance'] >= $starPrice): ?>
                    <button type="submit" class="btn btn-gold btn-block">Purchase Star +1</button>
                <?php else: ?>
                    <button type="button" class="btn btn-gold btn-block" disabled style="opacity:0.5;">Insufficient Funds</button>
                    <div class="text-red mt-1 text-center" style="font-size:11px;">You need <?= number_format($starPrice - $user['balance'], 2) ?> more coins.</div>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>

    <!-- Normal Boosts -->
    <div class="card">
        <h3 class="mb-1" style="font-size:18px;"><i class="fas fa-rocket text-blue"></i> Standard Boosts</h3>
        <p class="text-muted mb-2" style="font-size:14px; min-height:45px;">
            Boost your ranking score directly to gain an edge over the competition. Active duration stacks.
        </p>
        
        <form method="POST" action="/dashboard/server/<?= $server['id'] ?>/boost/purchase">
            <?= csrf_field() ?>
            
            <div class="form-group mb-2">
                <label>Select Package</label>
                <select name="package_id" class="form-control" required style="height:auto; padding:12px;">
                    <option value="">-- Choose a package --</option>
                    <?php foreach ($packages as $pkg): ?>
                        <option value="<?= $pkg['id'] ?>">
                            <?= e($pkg['name']) ?> — <?= number_format($pkg['price'], 2) ?> Coins (<?= $pkg['duration_days'] ?> Days / <?= $pkg['points'] ?> Pts)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Purchase Package</button>
        </form>
    </div>
</div>
