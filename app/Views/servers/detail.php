<?php
$layout = 'main';
$currentPage = 'servers';
$pageTitle = e($server['name']);
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', '/js/chart-init.js'];
?>

<div class="container">
    <!-- Header Card -->
    <div class="server-detail-header-card">
        <div class="server-detail-icon">
            <?php if (!empty($server['favicon_base64'])): ?>
                <img src="<?= e($server['favicon_base64']) ?>" alt="Icon">
            <?php else: ?>
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--bg-hover); border-radius: 16px; border: 2px solid var(--border-color);">
                    <i class="fas fa-cube fa-3x text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="server-detail-info" style="flex-grow: 1;">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 15px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <h1 class="server-detail-title" style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0; color: var(--text-primary);"><?= e($server['name']) ?></h1>
                    <?php if ($server['is_verified'] ?? false): ?>
                        <span class="text-green" title="Verified Server" style="font-size: 1.5rem;"><i class="fas fa-check-circle"></i></span>
                    <?php endif; ?>
                </div>
                
                <button class="btn btn-vote btn-lg" data-server-id="<?= $server['id'] ?>" onclick="voteServer(<?= $server['id'] ?>, this)">
                    <i class="fas fa-caret-up"></i> Vote (<?= (int)($server['vote_count'] ?? 0) ?>)
                </button>
            </div>
            
            <div class="flex gap-2 mt-3" style="flex-wrap:wrap; align-items: center;">
                <?php if ($server['is_online'] ?? false): ?>
                    <span class="status-badge" style="background: rgba(16,185,129,0.1); color: #10b981; padding: 6px 14px; border-radius: 20px; font-weight: 700; border: 1px solid rgba(16,185,129,0.2);">
                        <span class="pulse-dot"></span> Online
                    </span>
                <?php else: ?>
                    <span class="status-badge" style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 6px 14px; border-radius: 20px; font-weight: 700; border: 1px solid rgba(239,68,68,0.2);">
                        <i class="fas fa-circle" style="font-size: 8px;"></i> Offline
                    </span>
                <?php endif; ?>
                
                <span class="interactive-ip" onclick="copyIpAddress('<?= e($server['ip']) ?><?= $server['port'] != 25565 ? ':' . $server['port'] : '' ?>')" title="Click to copy IP">
                    <i class="fas fa-copy text-primary"></i> <span><?= e($server['ip']) ?><?= $server['port'] != 25565 ? ':' . $server['port'] : '' ?></span>
                </span>
                
                <?php if ($server['version'] ?? null): ?>
                    <span class="tag" style="font-size: 0.9rem; padding: 6px 12px;"><i class="fas fa-code-branch"></i> <?= e($server['version']) ?></span>
                <?php endif; ?>
                
                <span class="text-muted ml-auto" style="font-size: 0.95rem;">
                    Added by <a href="/user/<?= urlencode($server['owner_name'] ?? 'Unknown') ?>" style="color: var(--primary-color); font-weight: 600; text-decoration: none;"><i class="fas fa-user-circle"></i> <?= e($server['owner_name'] ?? 'Unknown') ?></a>
                </span>
            </div>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid-2-1">
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Stats -->
            <div class="server-detail-stats">
                <div class="stat-card">
                    <div class="stat-value"><i class="fas fa-users text-primary mb-2 d-block" style="font-size: 1.5rem; opacity: 0.8;"></i> <?= (int)($server['players_online'] ?? 0) ?>/<?= (int)($server['players_max'] ?? 0) ?></div>
                    <div class="stat-label">Players Online</div>
                    <?php
                        $max = (int)($server['players_max'] ?? 1) ?: 1;
                        $pct = round(((int)($server['players_online'] ?? 0) / $max) * 100);
                    ?>
                    <div class="progress-bar mt-3" style="height: 6px; border-radius: 3px; background: rgba(148, 163, 184, 0.1);">
                        <div class="progress-fill <?= $pct > 80 ? 'high' : '' ?>" style="width:<?= $pct ?>%; border-radius: 3px; background: var(--primary-color);"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><i class="fas fa-clock text-info mb-2 d-block" style="font-size: 1.5rem; opacity: 0.8;"></i> <?= round((float)($server['uptime_percent'] ?? 0), 1) ?>%</div>
                    <div class="stat-label">Uptime (7d)</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value text-gold"><i class="fas fa-star text-gold mb-2 d-block" style="font-size: 1.5rem; opacity: 0.8;"></i> <?= round((float)($server['rank_score'] ?? 0), 1) ?></div>
                    <div class="stat-label">Rating Score</div>
                </div>
            </div>

            <!-- Description -->
            <?php if ($server['description']): ?>
                <div class="card p-5">
                    <h3 class="section-title mb-4" style="font-family: var(--font-heading); font-size: 1.5rem;"><i class="fas fa-info-circle text-primary"></i> About <?= e($server['name']) ?></h3>
                    <div class="server-description" style="color:var(--text-secondary); line-height:1.8; font-size: 1.05rem; white-space: pre-wrap;"><?= e($server['description']) ?></div>
                    
                    <?php
                        $tags = json_decode($server['tags'] ?? '[]', true) ?: [];
                        if ($tags):
                    ?>
                        <hr style="border: none; border-top: 1px solid var(--border-color); opacity: 0.5; margin: 30px 0;">
                        <div class="d-flex" style="gap: 8px; flex-wrap: wrap;">
                            <?php foreach ($tags as $tag): ?>
                                <span class="tag"><i class="fas fa-hashtag text-muted"></i> <?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Interactive Charts -->
            <div class="card p-5">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 15px;">
                    <h3 class="section-title mb-0" style="font-family: var(--font-heading); font-size: 1.5rem;"><i class="fas fa-chart-area text-primary"></i> Player Activity</h3>
                    <div class="tabs" style="background: rgba(148, 163, 184, 0.05); padding: 4px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); display: inline-flex;">
                        <a class="tab active" data-period="24h" onclick="loadChart(<?= $server['id'] ?>, '24h', this)" style="padding: 6px 15px; border-radius: var(--radius-sm); cursor: pointer; text-decoration: none; color: var(--text-primary); font-weight: 500;">24 Hours</a>
                        <a class="tab" data-period="7d" onclick="loadChart(<?= $server['id'] ?>, '7d', this)" style="padding: 6px 15px; border-radius: var(--radius-sm); cursor: pointer; text-decoration: none; color: var(--text-primary); font-weight: 500;">7 Days</a>
                        <a class="tab" data-period="30d" onclick="loadChart(<?= $server['id'] ?>, '30d', this)" style="padding: 6px 15px; border-radius: var(--radius-sm); cursor: pointer; text-decoration: none; color: var(--text-primary); font-weight: 500;">30 Days</a>
                    </div>
                </div>
                <div class="chart-wrapper" style="position: relative; height: 320px; width: 100%;">
                    <canvas id="playersChart"></canvas>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="card p-5">
                <h3 class="section-title mb-4" style="font-family: var(--font-heading); font-size: 1.5rem;"><i class="fas fa-comments text-primary"></i> Player Reviews</h3>

                <?php if (!auth()): ?>
                    <div class="text-center p-4 mb-4" style="background: rgba(148, 163, 184, 0.05); border-radius: var(--radius-sm); border: 1px dashed var(--border-color);">
                        <i class="fas fa-lock text-muted fa-2x mb-2"></i>
                        <p class="text-muted mb-3">You must be logged in to leave a review.</p>
                        <a href="/login" class="btn btn-primary btn-sm">Log In / Register</a>
                    </div>
                <?php elseif (!$hasReviewed && auth()['id'] != $server['user_id']): ?>
                    <div class="review-form-container p-4 mb-4" style="background: rgba(148, 163, 184, 0.05); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <h4 class="mb-3" style="font-family: var(--font-heading);">Write a Review</h4>
                        <form action="/server/<?= $server['id'] ?>/review" method="POST">
                            <?= csrf_field() ?>
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap: 15px;">
                                <label class="mb-0 fw-bold">Overall Rating</label>
                                <div class="star-rating-form">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                        <label for="star<?= $i ?>"><i class="fas fa-star" style="font-size: 1.8rem; transition: 0.2s;"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <textarea name="comment" class="form-control" rows="3" minlength="10" required placeholder="What makes this server special? Explain your rating..." style="resize: vertical;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Publish Review</button>
                        </form>
                    </div>
                <?php elseif ($hasReviewed): ?>
                    <div class="text-center p-4 mb-4" style="background: rgba(16,185,129,0.05); border-radius: var(--radius-sm); border: 1px dashed rgba(16,185,129,0.3);">
                        <i class="fas fa-check-circle text-green fa-2x mb-2"></i>
                        <p class="mb-0 text-green fw-bold">You have already reviewed this server. Thank you!</p>
                    </div>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <div class="text-center text-muted p-5 text-lg" style="opacity: 0.6;">
                        <i class="fas fa-comment-slash fa-3x mb-3" style="opacity: 0.5;"></i>
                        <p class="mb-0 text-lg">No reviews yet. Be the first to share your experience!</p>
                    </div>
                <?php else: ?>
                    <div class="reviews-list d-flex flex-column" style="gap: 20px;">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="flex-between mb-3" style="align-items: flex-start;">
                                    <div class="d-flex align-items-center" style="gap: 15px;">
                                        <a href="/user/<?= urlencode($review['username']) ?>" style="text-decoration: none;">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($review['username']) ?>&background=random&color=fff&bold=true" alt="<?= e($review['username']) ?>" style="width: 48px; height: 48px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                        </a>
                                        <div>
                                            <div style="font-weight: 700; font-size: 1.1rem;">
                                                <a href="/user/<?= urlencode($review['username']) ?>" style="color: var(--text-color); text-decoration: none; transition: color 0.2s;"><?= e($review['username']) ?></a>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.85rem;"><i class="far fa-clock"></i> <?= date('M j, Y \a\t H:i', strtotime($review['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-gold" style="font-size: 1.1rem; background: rgba(245,158,11,0.1); padding: 4px 10px; border-radius: 20px;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $review['rating'] ? '' : 'text-muted' ?>" <?= $i > $review['rating'] ? 'style="opacity: 0.3;"' : '' ?>></i>
                                        <?php endfor; ?>
                                        <span class="fs-6 fw-bold text-dark ms-1 ml-1" style="color: var(--text-color) !important;"><?= $review['rating'] ?>.0</span>
                                    </div>
                                </div>
                                <p style="color: var(--text-secondary); margin: 0; font-size: 1.05rem; line-height: 1.6;">
                                    <?= nl2br(e($review['comment'])) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column Sidebar -->
        <div class="sticky-top" style="top: 25px; display: flex; flex-direction: column; gap: 24px;">
            <!-- Actions -->
            <div class="card p-4 text-center">
                <?php if ($server['website']): ?>
                    <a href="<?= e($server['website']) ?>" target="_blank" rel="noopener" class="btn btn-secondary w-100 mb-3 text-lg">
                        <i class="fas fa-globe"></i> Visit Website
                    </a>
                <?php endif; ?>
                
                <button class="btn btn-vote w-100 text-lg" data-server-id="<?= $server['id'] ?>" onclick="voteServer(<?= $server['id'] ?>, this)">
                    <i class="fas fa-heart"></i> Support Server
                </button>
                <div class="text-muted text-sm mt-3" style="opacity: 0.7;">You can vote once every 24 hours.</div>
            </div>

            <!-- Embed Banner -->
            <div class="card p-4">
                <h3 class="section-title mb-3" style="font-size: 1.25rem; font-family: var(--font-heading);"><i class="fas fa-code text-primary"></i> Embed Status</h3>
                <p class="text-muted text-sm mb-4" style="line-height: 1.6;">Copy this HTML code to show your server status dynamically on your website or forum!</p>
                <div class="mb-4 text-center">
                    <img src="/api/server/<?= $server['id'] ?>/banner" alt="<?= e($server['name']) ?> Banner" style="max-width: 100%; border-radius: var(--radius-sm); border: var(--glass-border); box-shadow: var(--card-shadow);">
                </div>
                <div class="form-group flex flex-column gap-2">
                    <?php 
                        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        $appUrl = rtrim(setting('app_url', $scheme . '://' . $_SERVER['HTTP_HOST']), '/');
                        $bannerUrl = $appUrl . '/api/server/' . $server['id'] . '/banner';
                        $serverUrl = $appUrl . '/server/' . $server['id'];
                        $embedCode = '<a href="' . $serverUrl . '"><img src="' . $bannerUrl . '" alt="' . htmlspecialchars($server['name'], ENT_QUOTES) . '"></a>';
                    ?>
                    <textarea class="form-control text-sm" rows="3" style="font-family: monospace; resize: none; background: rgba(148, 163, 184, 0.05);" readonly onclick="this.select();"><?= e($embedCode) ?></textarea>
                    <button class="btn w-100 btn-secondary mt-2" onclick="copyToClipboard('<?= e(addslashes($embedCode)) ?>', this)">
                        <i class="fas fa-copy"></i> Copy Code
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyIpAddress(ip) {
    navigator.clipboard.writeText(ip).then(() => {
        if (typeof showToast === 'function') {
            showToast('Server IP copied to clipboard!', 'success');
        } else {
            alert('IP copied: ' + ip);
        }
    }).catch(err => {
        console.error('Could not copy text: ', err);
    });
}

function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i> Copied!';
        if (typeof showToast === 'function') {
            showToast('Embed code copied!', 'success');
        }
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    });
}

// Ensure tabs style applies instantly on click
document.querySelectorAll('.tabs .tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        this.style.background = 'var(--primary-color)';
        this.style.color = '#fff';
        
        document.querySelectorAll('.tabs .tab:not(.active)').forEach(t => {
            t.style.background = 'transparent';
            t.style.color = 'var(--text-color)';
        });
    });
});

// Set initial tab styling
const activeTab = document.querySelector('.tabs .tab.active');
if(activeTab) {
    activeTab.style.background = 'var(--primary-color)';
    activeTab.style.color = '#fff';
}
</script>


<style>
.star-rating-form {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 8px;
}
.star-rating-form input {
    display: none;
}
.star-rating-form label {
    cursor: pointer;
    color: var(--border-color);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
}
.star-rating-form input:checked ~ label,
.star-rating-form input:checked ~ label:hover,
.star-rating-form label:hover,
.star-rating-form label:hover ~ label {
    color: #f59e0b; /* Bright amber gold */
    transform: scale(1.1);
    text-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background-color: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 1.5s infinite;
    display: inline-block;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

</style>
