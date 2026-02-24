<?php 
$layout = 'main'; 
$pageTitle = 'Two-Factor Verification'; 
?>

<div class="container" style="max-width: 420px; margin-top: 60px;">
    <div class="card" style="padding: 40px 30px; text-align: center;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary-color)15; color: var(--primary-color); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
            <i class="fas fa-shield-halved" style="font-size: 28px;"></i>
        </div>

        <h2 style="font-family: var(--font-heading); margin-bottom: 8px;">Two-Factor Verification</h2>
        <p class="text-muted mb-4">Enter the code from your authenticator app or a backup code.</p>

        <form action="/2fa/verify" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <input type="text" name="code" class="form-control" placeholder="000000" maxlength="8" required autofocus
                       style="font-size: 1.5rem; text-align: center; letter-spacing: 6px; font-weight: 700;">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Verify</button>
        </form>

        <a href="/login" class="d-block mt-3 text-muted" style="font-size: 0.9rem;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Login
        </a>
    </div>
</div>
