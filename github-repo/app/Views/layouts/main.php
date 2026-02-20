<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription ?? 'Minecraft Server Monitoring Platform — Track, rate and discover the best Minecraft servers') ?>">
    <title><?= e($pageTitle ?? 'MC Monitor') ?> — Minecraft Server Monitoring</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="/" class="navbar-brand">
                <span class="brand-icon"><i class="fas fa-cubes"></i></span>
                <span>MC Monitor</span>
            </a>

            <button class="nav-mobile-toggle" id="navToggle"><i class="fas fa-bars"></i></button>

            <ul class="navbar-nav" id="navMenu">
                <li><a href="/" class="<?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
                <li><a href="/servers" class="<?= ($currentPage ?? '') === 'servers' ? 'active' : '' ?>">Servers</a></li>
                <li><a href="/posts" class="<?= ($currentPage ?? '') === 'posts' ? 'active' : '' ?>">News</a></li>
            </ul>

            <div class="navbar-right">
                <button class="theme-toggle" id="themeToggle" title="Toggle theme"><i class="fas fa-moon"></i></button>
                <?php if (auth()): ?>
                    <a href="/dashboard" class="btn btn-sm btn-secondary">
                        <?= e(auth()['username']) ?>
                    </a>
                    <?php if (is_admin()): ?>
                        <a href="/admin" class="btn btn-sm btn-gold">Admin</a>
                    <?php endif; ?>
                    <a href="/logout" class="btn btn-sm btn-danger">Exit</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-sm btn-secondary">Login</a>
                    <a href="/register" class="btn btn-sm btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash = flash('success')): ?>
        <div class="toast">
            <div class="alert alert-success"><?= e($flash) ?></div>
        </div>
    <?php endif; ?>
    <?php if ($flash = flash('error')): ?>
        <div class="toast">
            <div class="alert alert-error"><?= e($flash) ?></div>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <main class="page-content">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="flex-center gap-2 mb-1" style="font-size:13px;">
                <a href="/servers" style="color:var(--text-muted);">Servers</a>
                <span style="opacity:0.3;">&middot;</span>
                <a href="/posts" style="color:var(--text-muted);">News</a>
                <span style="opacity:0.3;">&middot;</span>
                <a href="/register" style="color:var(--text-muted);">Add Server</a>
            </div>
            <p>&copy; <?= date('Y') ?> MC Monitor</p>
        </div>
    </footer>

    <script src="/js/app.js"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
