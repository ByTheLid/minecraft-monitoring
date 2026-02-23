<?php
$layout = 'main';
$currentPage = 'users';
$pageTitle = 'Top Users';
?>

<div class="container">
    <div class="text-center mb-5 mt-3">
        <h1 class="page-title text-primary" style="font-size: 2.5rem; margin-bottom: 8px;"><i class="fas fa-trophy text-gold"></i> Top Users</h1>
        <p class="text-muted text-lg">Discover the most active members of our community</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
        <?php foreach ($users as $i => $u): 
            $userAchievements = $achievementsByUserId[$u['id']] ?? [];
            $avatar = \App\Models\User::getAvatar($u, $userAchievements);
        ?>
            <a href="/user/<?= urlencode($u['username']) ?>" class="card" style="display: flex; flex-direction: column; text-decoration: none; position: relative; overflow: hidden; padding: 24px; transition: var(--transition);">
                
                <!-- Rank ribbon for Top 3 -->
                <?php if ($i < 3): ?>
                    <div style="position: absolute; top: 0; right: 0; background: <?= $i === 0 ? 'var(--accent-gold)' : ($i === 1 ? '#94a3b8' : '#cd7f32') ?>; color: <?= $i === 0 ? '#000' : '#fff' ?>; padding: 4px 16px; font-weight: 800; font-size: 0.9rem; border-bottom-left-radius: 12px; z-index: 1;">
                        #<?= $i + 1 ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex align-items-center mb-4" style="gap: 16px;">
                    <img src="<?= $avatar['url'] ?>" alt="<?= e($u['username']) ?>" style="width: 64px; height: 64px; border-radius: 50%; <?= $avatar['style'] ?>; object-fit: cover; border: 2px solid var(--bg-body);">
                    
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="margin: 0; font-size: 1.3rem; color: var(--text-primary); font-family: var(--font-heading); font-weight: 700; display: flex; align-items: center; gap: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= e($u['username']) ?>
                            <?php if ($u['role'] === 'admin'): ?>
                                <i class="fas fa-crown text-gold" style="font-size: 0.9rem;" title="Admin"></i>
                            <?php endif; ?>
                        </h3>
                        <div class="text-muted" style="font-size: 0.85rem; margin-top: 4px;"><i class="far fa-clock"></i> Joined <?= date('M Y', strtotime($u['created_at'])) ?></div>
                    </div>
                </div>

                <?php if (!empty($u['bio'])): ?>
                    <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                        <?= e($u['bio']) ?>
                    </p>
                <?php else: ?>
                    <p style="color: var(--text-secondary); font-size: 0.95rem; font-style: italic; margin-bottom: 20px; flex-grow: 1; opacity: 0.6;">No bio provided.</p>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: auto;">
                    <div class="text-center">
                        <div style="font-weight: 800; font-size: 1.2rem; color: var(--text-primary);"><?= number_format($u['total_votes']) ?></div>
                        <div class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;">Votes</div>
                    </div>
                    <div class="text-center" style="border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">
                        <div style="font-weight: 800; font-size: 1.2rem; color: var(--accent-green);"><?= $u['total_achievements'] ?></div>
                        <div class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;">Trophies</div>
                    </div>
                    <div class="text-center">
                        <div style="font-weight: 800; font-size: 1.2rem; color: var(--accent-blue);"><?= $u['total_servers'] ?></div>
                        <div class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;">Servers</div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>


