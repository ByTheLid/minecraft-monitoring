<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription ?? setting('site_description', 'Minecraft Server Monitoring Platform — Track, rate and discover the best Minecraft servers')) ?>">
    <meta name="keywords" content="<?= e(setting('seo_keywords', 'minecraft, servers, monitoring, top')) ?>">
    <title><?= e($pageTitle ?? setting('site_name', 'MC Monitor')) ?></title>
    <link rel="icon" href="<?= e(setting('asset_favicon', '/favicon.ico')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php 
        $designPref = 'modern';
        if (auth() && isset(auth()['design_preference'])) {
            $designPref = auth()['design_preference'];
        } elseif (isset($_COOKIE['design_preference'])) {
            $designPref = $_COOKIE['design_preference'];
        }
        $cssFile = $designPref === 'pixel' ? 'style-pixel.css' : 'style-modern.css';
    ?>
    <link rel="stylesheet" href="/css/<?= $cssFile ?>?v=<?= time() ?>">
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
                <?php if ($logo = setting('asset_logo', '')): ?>
                    <img src="<?= e($logo) ?>" alt="<?= e(setting('site_name', 'MC Monitor')) ?>" style="max-height: 32px; width: auto; max-width: 150px; object-fit: contain;">
                <?php else: ?>
                    <span class="brand-icon"><i class="fas fa-cubes"></i></span>
                    <span><?= e(setting('site_name', 'MC Monitor')) ?></span>
                <?php endif; ?>
            </a>

            <button class="nav-mobile-toggle" id="navToggle"><i class="fas fa-bars"></i></button>

            <ul class="navbar-nav" id="navMenu">
                <li><a href="/" class="<?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
                <li><a href="/servers" class="<?= ($currentPage ?? '') === 'servers' ? 'active' : '' ?>">Servers</a></li>
                <li><a href="/posts" class="<?= ($currentPage ?? '') === 'posts' ? 'active' : '' ?>">News</a></li>
            </ul>

            <div class="navbar-right">
                <button class="theme-toggle" id="designToggle" title="Toggle Design Version (Modern/Pixel)">
                    <i class="fas <?= $designPref === 'pixel' ? 'fa-gamepad' : 'fa-paint-brush' ?>"></i>
                </button>
                <button class="theme-toggle" id="themeToggle" title="Toggle theme"><i class="fas fa-moon"></i></button>
                <?php if (auth()): ?>
                    <a href="/dashboard" class="btn btn-sm btn-secondary">
                        <i class="fas fa-user"></i> <?= e(auth()['username']) ?>
                    </a>
                    <?php if (is_admin()): ?>
                        <a href="/admin" class="btn btn-sm btn-gold"><i class="fas fa-cogs"></i> Admin</a>
                    <?php endif; ?>
                    <a href="/logout" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Exit</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-sm btn-secondary">Login</a>
                    <a href="/register" class="btn btn-sm btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
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
    <!-- Vote Modal -->
    <div id="voteModalBackdrop" class="modal-backdrop">
        <div class="modal">
            <h2>Vote for Server</h2>
            <p style="color:var(--text-secondary); margin-bottom:15px;">Please enter your Minecraft username to receive rewards.</p>
            <div class="form-group">
                <label for="voteUsername">Username</label>
                <input type="text" id="voteUsername" class="form-control" placeholder="Steve" required>
            </div>
            <div class="flex" style="justify-content:flex-end; gap:10px; margin-top:20px;">
                <button class="btn btn-secondary" id="closeVoteModal">Cancel</button>
                <button class="btn btn-primary" id="confirmVoteBtn">Vote</button>
            </div>
        </div>
    </div>

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
            <p>&copy; <?= date('Y') ?> <?= e(setting('site_name', 'MC Monitor')) ?></p>
            
            <?php 
                $socials = [
                    'discord' => ['icon' => 'fa-discord', 'url' => setting('social_discord')],
                    'telegram' => ['icon' => 'fa-telegram', 'url' => setting('social_telegram')],
                    'vk' => ['icon' => 'fa-vk', 'url' => setting('social_vk')]
                ];
            ?>
            <div class="flex-center gap-2 mt-1">
                <?php foreach ($socials as $id => $data): ?>
                    <?php if (!empty($data['url'])): ?>
                        <a href="<?= e($data['url']) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--text-muted); font-size:18px; transition: color 0.3s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-muted)'">
                            <i class="fab <?= $data['icon'] ?>"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
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
