<?php $layout = 'main'; $currentPage = 'register'; $pageTitle = 'Register'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Register</h1>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       value="<?= e(old('username')) ?>" placeholder="3-32 characters, letters/numbers/_" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= e(old('email')) ?>" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Minimum 8 characters" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                       placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="/login">Login</a>
        </div>
    </div>
</div>
