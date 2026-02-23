<?php
$layout = 'main';
$pageTitle = 'Reset Password';
?>

<div class="container" style="max-width: 500px; margin-top: 50px;">
    <div class="card p-4">
        <h2 class="text-center mb-3">Set New Password</h2>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger mb-3"><?= e($error) ?></div>
        <?php endif; ?>

        <form action="/reset-password" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
            
            <div class="form-group mb-3">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required minlength="8" autofocus>
            </div>

            <div class="form-group mb-4">
                <label for="password_confirm">Confirm New Password</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••" required minlength="8">
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Change Password</button>
        </form>
    </div>
</div>
