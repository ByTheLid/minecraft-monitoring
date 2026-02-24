<?php 
$layout = 'main'; 
$pageTitle = 'Monthly Leaderboard'; 

$rankColors = [
    'Novice' => '#94a3b8', 'Bronze' => '#cd7f32', 'Silver' => '#c0c0c0',
    'Gold' => '#fbbf24', 'Diamond' => '#22d3ee', 'Legendary' => '#f43f5e',
];

$medals = ['🥇', '🥈', '🥉'];
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="section-title mb-1" style="font-size: 2rem;"><i class="fas fa-ranking-star text-gold mr-2"></i> Monthly Leaderboard</h1>
            <p class="text-muted mb-0">Top voters of the month — compete for glory!</p>
        </div>

        <form method="GET" action="/leaderboard" class="d-flex align-items-center" style="gap: 10px;">
            <select name="month" onchange="this.form.submit()" class="form-control" style="width: auto; min-width: 160px;">
                <?php foreach ($months as $m): ?>
                    <option value="<?= $m ?>" <?= $m === $currentMonth ? 'selected' : '' ?>>
                        <?= date('F Y', strtotime($m . '-01')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($leaderboard)): ?>
        <div class="card text-center" style="padding: 80px 40px; border: 2px dashed rgba(148, 163, 184, 0.2); background: rgba(148, 163, 184, 0.02);">
            <i class="fas fa-hourglass-half mb-4 text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            <h3 style="font-family: var(--font-heading); font-size: 1.75rem; color: var(--text-primary); margin-bottom: 8px;">No Data Yet</h3>
            <p class="text-muted text-lg mb-0">No votes have been recorded for <?= date('F Y', strtotime($currentMonth . '-01')) ?> yet.</p>
        </div>
    <?php else: ?>
        <div class="card p-0 overflow-hidden" style="border-radius: 16px; border: 1px solid var(--border-color);">
            <table class="admin-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">#</th>
                        <th>Player</th>
                        <th>Rank</th>
                        <th style="text-align: center;">Votes</th>
                        <th style="text-align: center;">XP Earned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $i => $entry): 
                        $pos = $i + 1;
                        $color = $rankColors[$entry['user_rank'] ?? 'Novice'] ?? '#94a3b8';
                        $isMedal = $pos <= 3;
                        $rowStyle = $isMedal ? "background: {$color}08; border-left: 3px solid {$color};" : '';
                    ?>
                        <tr style="<?= $rowStyle ?>">
                            <td style="text-align: center; font-weight: 800; font-size: 1.1rem;">
                                <?php if ($isMedal): ?>
                                    <span style="font-size: 1.5rem;"><?= $medals[$i] ?></span>
                                <?php else: ?>
                                    <span class="text-muted"><?= $pos ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/profile/<?= e($entry['username']) ?>" style="color: var(--text-primary); text-decoration: none; font-weight: 700; font-size: 1.05rem;">
                                    <?= e($entry['username']) ?>
                                </a>
                            </td>
                            <td>
                                <span style="color: <?= $color ?>; font-weight: 700; font-size: 0.9rem;">
                                    <i class="fas fa-shield-halved mr-1"></i> <?= e($entry['user_rank'] ?? 'Novice') ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span style="font-weight: 800; font-size: 1.1rem; color: var(--accent-green);">
                                    <?= number_format($entry['vote_count']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span style="font-weight: 600; color: var(--text-secondary);">
                                    +<?= number_format($entry['points_earned']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
