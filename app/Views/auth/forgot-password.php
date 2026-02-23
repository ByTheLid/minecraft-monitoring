<?php
$layout = 'main';
$pageTitle = 'Forgot Password';
?>

<div class="container" style="max-width: 500px; margin-top: 50px;">
    <div class="card p-4">
        <h2 class="text-center mb-3">Forgot Password</h2>
        
        <p class="text-center text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>

        <?php if ($success = flash('success')): ?>
            <div class="alert alert-success mb-3"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger mb-3"><?= e($error) ?></div>
        <?php endif; ?>

        <form action="/forgot-password" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group mb-3">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required autofocus placeholder="you@example.com">
            </div>

            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>

        <div class="text-center mt-3">
            <a href="/login" class="text-muted" style="text-decoration: underline;">Back to Login</a>
        </div>
    </div>
</div>
