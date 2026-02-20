<?php $layout = 'main'; $currentPage = 'login'; $pageTitle = 'Login'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Login</h1>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="login">Username or Email</label>
                <input type="text" id="login" name="login" class="form-control"
                       value="<?= e(old('login')) ?>" placeholder="Enter username or email" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="/register">Register</a>
        </div>
    </div>
</div>
