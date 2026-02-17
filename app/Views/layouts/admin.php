<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= e($pageTitle ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/admin" class="navbar-brand">
                <span class="brand-icon"><i class="fas fa-cogs"></i></span>
                <span>MC Admin</span>
            </a>
            <div class="navbar-right">
                <a href="/" class="btn btn-sm btn-secondary"><i class="fas fa-external-link-alt"></i> View Site</a>
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
            <a href="/admin/boost" class="<?= ($adminPage ?? '') === 'boost' ? 'active' : '' ?>"><i class="fas fa-rocket"></i> Boost</a>
            <a href="/admin/settings" class="<?= ($adminPage ?? '') === 'settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a>
        </aside>

        <div class="admin-content">
            <?php if ($flash = flash('success')): ?>
                <div class="alert alert-success"><?= e($flash) ?></div>
            <?php endif; ?>
            <?php if ($flash = flash('error')): ?>
                <div class="alert alert-error"><?= e($flash) ?></div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </div>
    </div>

    <script src="/js/app.js"></script>
</body>
</html>
