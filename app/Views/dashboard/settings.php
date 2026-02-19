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
