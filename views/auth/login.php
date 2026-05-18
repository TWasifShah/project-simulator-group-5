<?php require app_root() . '/views/layout/header.php'; ?>
<section class="card auth-card">
    <h1>Login</h1>
    <p class="muted">Demo admin: admin@example.com / Admin12345</p>
    <p class="muted">Demo member: member@example.com / Member12345</p>
    <?php if (!empty($errors['login'])): ?><div class="alert error"><?= e($errors['login']) ?></div><?php endif; ?>
    <form id="loginForm" method="post" action="<?= url('login') ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        <span class="field-error"><?= e($errors['email'] ?? '') ?></span>
        <label>Password</label>
        <input type="password" name="password" required>
        <span class="field-error"><?= e($errors['password'] ?? '') ?></span>
        <label class="checkbox-line"><input type="checkbox" name="remember" value="1"> Remember Me</label>
        <button class="btn" type="submit">Login</button>
    </form>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
