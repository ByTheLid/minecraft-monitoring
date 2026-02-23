<?php $layout = 'main'; $currentPage = 'settings'; $pageTitle = 'Settings'; ?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Account Settings</h1>
        <a href="/dashboard" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if ($flash = flash('success')): ?>
        <div class="alert alert-success"><?= e($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = flash('error')): ?>
        <div class="alert alert-error"><?= e($flash) ?></div>
    <?php endif; ?>

    <div class="card">
        <form action="/dashboard/settings" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group mb-2">
                <label>Username</label>
                <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                <small class="text-muted">Username cannot be changed.</small>
            </div>

            <div class="form-group mb-2">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
            </div>

            <hr class="mb-2">
            <h3 class="section-title mb-2">Public Profile</h3>

            <div class="form-group mb-2">
                <label>Design Theme</label>
                <select name="design_preference" class="form-control">
                    <option value="aesthetic" <?= ($user['design_preference'] ?? '') === 'aesthetic' ? 'selected' : '' ?>>Aesthetic (Modern & Beautiful)</option>
                    <option value="lightweight" <?= ($user['design_preference'] ?? '') === 'lightweight' ? 'selected' : '' ?>>Lightweight (Fast & Simple)</option>
                </select>
                <small class="text-muted">Choose your preferred visual style for the platform.</small>
            </div>

            <div class="form-group mb-2">
                <label>Bio / About Me</label>
                <textarea name="bio" class="form-control" rows="3" placeholder="Tell the community about yourself..."><?= e($user['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-group mb-2">
                <label>Discord Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fab fa-discord"></i></span>
                    <input type="text" name="social_discord" class="form-control" value="<?= e($user['social_discord'] ?? '') ?>" placeholder="username">
                </div>
            </div>

            <div class="form-group mb-2">
                <label>Telegram</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fab fa-telegram"></i></span>
                    <input type="text" name="social_telegram" class="form-control" value="<?= e($user['social_telegram'] ?? '') ?>" placeholder="t.me/username">
                </div>
            </div>

            <hr class="mb-2">
            <h3 class="section-title mb-2">Change Password</h3>

            <div class="form-group mb-2">
                <label>Current Password</label>
                <input type="password" name="password" class="form-control" placeholder="Required only if changing password">
            </div>

            <div class="form-group mb-2">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters">
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>
