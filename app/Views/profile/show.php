<?php 
$layout = 'main'; 
$pageTitle = e($user['username']) . "'s Profile"; 

// Generate dynamic avatar
$avatar = \App\Models\User::getAvatar($user, $achievements ?? []);
?>

<div class="container">
    <!-- Profile Header Banner -->
    <div class="card mb-4" style="position: relative; overflow: hidden; padding: 0; border: 1px solid var(--border-color); border-radius: var(--radius);">
        <div style="height: 140px; background: linear-gradient(135deg, var(--bg-secondary) 0%, rgba(59, 130, 246, 0.1) 100%); border-bottom: 1px solid var(--border-color);"></div>
        <div style="padding: 0 40px 40px 40px; display: flex; flex-direction: column; align-items: center; text-align: center; margin-top: -70px;">
            <div style="position: relative; margin-bottom: 20px;">
                <img src="<?= $avatar['url'] ?>" alt="<?= e($user['username']) ?>" style="width: 140px; height: 140px; border-radius: 50%; border: 6px solid var(--bg-card); <?= $avatar['style'] ?>; background: var(--bg-card); object-fit: cover;">
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <div style="position: absolute; bottom: 0; right: 0; background: var(--accent-gold); color: #000; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid var(--bg-card);" title="Admin">
                        <i class="fas fa-crown" style="font-size: 14px;"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; margin-bottom: 5px; color: var(--text-primary);">
                <?= e($user['username']) ?>
            </h1>
            <p class="text-muted mb-3" style="font-size: 0.95rem;"><i class="far fa-clock"></i> Joined <?= date('F j, Y', strtotime($user['created_at'])) ?></p>

            <?php if (!empty($user['bio'])): ?>
                <p style="max-width: 600px; color: var(--text-secondary); line-height: 1.6; margin: 0 auto 24px auto; font-size: 1.05rem;">
                    <?= e($user['bio']) ?>
                </p>
            <?php endif; ?>

            <div class="d-flex justify-content-center" style="gap: 12px; flex-wrap: wrap; margin-bottom: 30px;">
                <?php if (!empty($user['social_discord'])): ?>
                    <div class="tag" style="background: rgba(88, 101, 242, 0.1); border: 1px solid rgba(88, 101, 242, 0.2); color: #5865F2; padding: 8px 16px; font-weight: 600;">
                        <i class="fab fa-discord mr-2"></i> <?= e($user['social_discord']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($user['social_telegram'])): ?>
                    <a href="https://t.me/<?= e(ltrim($user['social_telegram'], '@')) ?>" target="_blank" class="tag" style="background: rgba(0, 136, 204, 0.1); border: 1px solid rgba(0, 136, 204, 0.2); color: #0088cc; padding: 8px 16px; font-weight: 600; text-decoration: none;">
                        <i class="fab fa-telegram mr-2"></i> <?= e($user['social_telegram']) ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Profile Quick Stats -->
            <div style="display: flex; gap: 40px; justify-content: center; border-top: 1px solid var(--border-color); padding-top: 30px; width: 100%; max-width: 800px;">
                <div class="text-center">
                    <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--accent-green); line-height: 1;"><?= count($achievements ?? []) ?></div>
                    <div class="text-muted text-uppercase mt-2" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px;">Trophies</div>
                </div>
                <div class="text-center" style="padding: 0 40px; border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">
                    <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--accent-blue); line-height: 1;"><?= count($servers ?? []) ?></div>
                    <div class="text-muted text-uppercase mt-2" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px;">Servers</div>
                </div>
                <div class="text-center">
                    <?php
                        // calculate total votes for aesthetic alignment
                        $totalVotes = 0;
                        if (!empty($servers)) {
                            foreach($servers as $srv) {
                                $totalVotes += (int)($srv['vote_count'] ?? 0);
                            }
                        }
                    ?>
                    <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--text-primary); line-height: 1;"><?= number_format($totalVotes) ?></div>
                    <div class="text-muted text-uppercase mt-2" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px;">Votes</div>
                </div>
            </div>

            <?php
                // Rank color mapping
                $rankColors = [
                    'Novice' => '#94a3b8', 'Bronze' => '#cd7f32', 'Silver' => '#c0c0c0',
                    'Gold' => '#fbbf24', 'Diamond' => '#22d3ee', 'Legendary' => '#f43f5e',
                ];
                $rankColor = $rankColors[$rankData['current']] ?? '#94a3b8';
            ?>

            <!-- Rank Progress -->
            <div class="mt-4 pt-4 w-100" style="border-top: 1px solid var(--border-color); max-width: 800px;">
                <div class="card" style="padding: 24px 30px; border-radius: 16px; border: 1px solid <?= $rankColor ?>30; background: <?= $rankColor ?>08;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center" style="gap: 12px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: <?= $rankColor ?>20; border: 2px solid <?= $rankColor ?>; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-shield-halved" style="font-size: 18px; color: <?= $rankColor ?>;"></i>
                            </div>
                            <div>
                                <div style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 800; color: <?= $rankColor ?>;">
                                    <?= e($rankData['current']) ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.85rem;">
                                    <?= number_format($rankData['points']) ?> XP
                                </div>
                            </div>
                        </div>
                        <?php if ($rankData['next']): ?>
                            <div class="text-right">
                                <div class="text-muted" style="font-size: 0.8rem;">Next rank</div>
                                <div style="font-weight: 700; color: var(--text-primary);"><?= e($rankData['next']) ?></div>
                            </div>
                        <?php else: ?>
                            <div style="font-weight: 700; color: <?= $rankColor ?>;">
                                <i class="fas fa-crown mr-1"></i> Max Rank
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Progress Bar -->
                    <div style="width: 100%; height: 10px; background: var(--bg-body); border-radius: 10px; overflow: hidden; position: relative;">
                        <div class="rank-progress-fill" style="
                            width: <?= $rankData['progress'] ?>%;
                            height: 100%;
                            background: linear-gradient(90deg, <?= $rankColor ?>, <?= $rankColor ?>cc);
                            border-radius: 10px;
                            transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
                            box-shadow: 0 0 8px <?= $rankColor ?>80;
                        "></div>
                    </div>

                    <?php if ($rankData['next']): ?>
                        <div class="d-flex justify-content-between mt-2" style="font-size: 0.8rem;">
                            <span class="text-muted"><?= number_format($rankData['prevThreshold']) ?> XP</span>
                            <span style="color: var(--text-primary); font-weight: 600;">
                                <?= number_format($rankData['remaining']) ?> XP remaining
                            </span>
                            <span class="text-muted"><?= number_format($rankData['nextThreshold']) ?> XP</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($achievements)): ?>
                <div class="mt-4 pt-4 w-100" style="border-top: 1px solid var(--border-color); max-width: 800px; text-align: left;">
                    <h4 class="mb-3" style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--text-primary); text-align: center;"><i class="fas fa-trophy text-gold mr-2"></i> Earned Trophies</h4>
                    <div class="d-flex justify-content-center" style="gap: 12px; flex-wrap: wrap;">
                        <?php foreach ($achievements as $badge): ?>
                            <div title="Unlocked <?= date('M d, Y', strtotime($badge['unlocked_at'])) ?>: <?= e($badge['description']) ?>" style="background: <?= $badge['color'] ?>15; color: <?= $badge['color'] ?>; border: 1px solid <?= $badge['color'] ?>40; border-radius: var(--radius-sm); padding: 8px 16px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px;">
                                <i class="<?= $badge['icon'] ?>" style="font-size: 16px;"></i>
                                <span><?= e($badge['title']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid-2-1">
        <!-- Left Column: Servers -->
        <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="section-title mb-0"><i class="fas fa-server text-muted"></i> Managed Servers</h2>
            </div>

            <?php if (empty($servers)): ?>
                <div class="card text-center" style="padding: 80px 40px; border: 2px dashed rgba(148, 163, 184, 0.2); background: rgba(148, 163, 184, 0.02); border-radius: var(--radius); box-shadow: none;">
                    <i class="fas fa-box-open mb-4 text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h3 style="font-family: var(--font-heading); font-size: 1.75rem; color: var(--text-primary); margin-bottom: 8px;">No Active Servers</h3>
                    <p class="text-muted text-lg mb-0"><?= e($user['username']) ?> hasn't listed any servers yet.</p>
                </div>
            <?php else: ?>
                <div class="grid-2">
                    <?php foreach ($servers as $server): ?>
                        <div class="card server-card" style="padding: 25px; border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <?php if (!empty($server['favicon_base64'])): ?>
                                    <img src="<?= $server['favicon_base64'] ?>" class="server-icon-lg" alt="Icon" style="width: 72px; height: 72px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <?php else: ?>
                                    <div class="server-icon-placeholder-lg" style="width: 72px; height: 72px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--bg-body); border: 2px solid var(--border-color); font-size: 28px;"><i class="fas fa-cube text-muted"></i></div>
                                <?php endif; ?>

                                <div class="server-meta" style="min-height: 24px;">
                                    <?php if ($server['is_online']): ?>
                                        <span class="status-badge" style="background: rgba(16,185,129,0.1); color: #10b981; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(16,185,129,0.2);">
                                            <div class="pulse-dot"></div> ONLINE
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge" style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(239,68,68,0.2);">
                                            <i class="fas fa-circle" style="font-size: 8px;"></i> OFFLINE
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <h3 class="server-title mb-3" style="font-size: 1.4rem;">
                                <a href="/server/<?= $server['id'] ?>" style="color: var(--text-color); text-decoration: none; font-weight: 800;"><?= e($server['name']) ?></a>
                            </h3>

                            <div class="server-stats flex-between" style="background: var(--bg-body); padding: 12px 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                                <span class="d-flex align-items-center gap-2" title="Players Online">
                                    <i class="fas fa-users text-primary"></i> <span style="font-weight: 700; font-size: 1.1rem;"><?= number_format($server['players_online']) ?></span>
                                </span>
                                <span class="d-flex align-items-center gap-2" title="Total Votes">
                                    <i class="fas fa-thumbs-up text-primary"></i> <span style="font-weight: 700; font-size: 1.1rem;"><?= number_format($server['vote_count']) ?></span>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Recent Activity -->
        <div class="sticky-top" style="top: 25px;">
            <h2 class="section-title mb-3"><i class="fas fa-bolt text-warning"></i> Recent Activity</h2>
            <?php if (empty($votes)): ?>
                <div class="card p-5 text-center text-muted" style="border: 2px dashed rgba(148, 163, 184, 0.2); background: rgba(148, 163, 184, 0.02); border-radius: var(--radius); box-shadow: none;">
                    <i class="fas fa-bed mb-4" style="font-size: 3rem; opacity: 0.2;"></i>
                    <p class="mb-0 text-lg">No recent votes recorded.</p>
                </div>
            <?php else: ?>
                <div class="card p-0 overflow-hidden" style="border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <ul class="list-group" style="list-style: none; margin: 0; padding: 0;">
                        <?php foreach (array_slice($votes, 0, 8) as $vote): // Limit to 8 recent votes for clean UI ?>
                            <li class="list-group-item" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background: transparent; transition: background 0.2s;">
                                <div class="d-flex" style="gap: 15px; align-items: center;">
                                    <div class="vote-icon" style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary-color)20; color: var(--primary-color); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 10px var(--primary-color)20;">
                                        <i class="fas fa-arrow-up"></i>
                                    </div>
                                    <div style="flex-grow: 1;">
                                        <div style="font-size: 1rem; font-weight: 500;">Placed a vote on</div>
                                        <div><a href="/server/<?= $vote['server_id'] ?>" style="color: var(--text-color); text-decoration: none; font-weight: 700; font-size: 1.1rem;"><?= e($vote['server_name'] ?: 'Unknown Server') ?></a></div>
                                        <div class="text-sm text-muted mt-1"><i class="far fa-clock"></i> <?= time_ago($vote['voted_at']) ?></div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.pulse-dot {
    width: 10px;
    height: 10px;
    background-color: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 1.5s infinite;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.server-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
}
.list-group-item:hover {
    background: var(--bg-hover) !important;
}
@media (max-width: 768px) {
    .profile-stats {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
