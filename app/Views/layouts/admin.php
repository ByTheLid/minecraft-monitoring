<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= e($pageTitle ?? 'Dashboard') ?> | <?= e(setting('site_name', 'MC Monitor')) ?></title>
    <link rel="icon" href="<?= e(setting('asset_favicon', '/favicon.ico')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php 
        $designPref = auth()['design_preference'] ?? 'modern';
        $cssFile = $designPref === 'pixel' ? 'style-pixel.css' : 'style-modern.css';
    ?>
    <link rel="stylesheet" href="/css/<?= $cssFile ?>?v=<?= time() ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/admin" class="navbar-brand">
                <span class="brand-icon"><i class="fas fa-cogs"></i></span>
                <span>MC Admin</span>
            </a>
            <div class="navbar-right" style="display: flex; align-items: center; gap: 10px;">
                <a href="/" class="btn btn-sm btn-secondary"><i class="fas fa-external-link-alt"></i> View Site</a>
                <button class="theme-toggle" id="designToggle" title="Toggle Design Version (Modern/Pixel)" style="background: none; border: none; cursor: pointer; color: var(--text-color); font-size: 1.2rem;">
                    <i class="fas <?= $designPref === 'pixel' ? 'fa-gamepad' : 'fa-paint-brush' ?>"></i>
                </button>
                <a href="/logout" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Exit</a>
            </div>
        </div>
    </nav>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="/admin" class="<?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Dashboard</a>
            <a href="/admin/servers?filter=pending" class="<?= ($adminPage ?? '') === 'servers' ? 'active' : '' ?>"><i class="fas fa-server"></i> Servers</a>
            <a href="/admin/users" class="<?= ($adminPage ?? '') === 'users' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a>
            <a href="/admin/posts" class="<?= ($adminPage ?? '') === 'posts' ? 'active' : '' ?>"><i class="fas fa-newspaper"></i> Posts</a>
            <a href="/admin/analytics" class="<?= ($adminPage ?? '') === 'analytics' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Analytics</a>
            <a href="/admin/boost" class="<?= ($adminPage ?? '') === 'boost' ? 'active' : '' ?>"><i class="fas fa-rocket"></i> Boost</a>
            <a href="/admin/settings" class="<?= ($adminPage ?? '') === 'settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a>
        </aside>

        <div class="admin-content">
            <div class="toast-container" id="toastContainer">
                <?php if ($flash = flash('success')): ?>
                    <div class="toast success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= e($flash) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($flash = flash('error')): ?>
                    <div class="toast error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= e($flash) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <?= $content ?? '' ?>
        </div>
    </div>

    <script src="/js/app.js"></script>
    <?php if (isset($extraJs) && is_array($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
