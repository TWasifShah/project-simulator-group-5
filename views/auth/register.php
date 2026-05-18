<?php require app_root() . '/views/layout/header.php'; ?>
<section class="card auth-card">
    <h1>Create Account</h1>
    <p class="muted">Register as ADMIN or MEMBER.</p>
    <?php if (!empty($errors['login'])): ?><div class="alert error"><?= e($errors['login']) ?></div><?php endif; ?>
    <form id="registerForm" method="post" action="<?= url('register') ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Name</label>
        <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
        <span class="field-error"><?= e($errors['name'] ?? '') ?></span>

        <label>Email</label>
        <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        <span class="field-error"><?= e($errors['email'] ?? '') ?></span>

        <label>Password</label>
        <input type="password" name="password" minlength="8" required>
        <span class="field-error"><?= e($errors['password'] ?? '') ?></span>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" minlength="8" required>
        <span class="field-error"><?= e($errors['confirm_password'] ?? '') ?></span>

        <label>Role</label>
        <select name="role" required>
            <option value="member" <?= ($_POST['role'] ?? '') === 'member' ? 'selected' : '' ?>>Member</option>
            <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <span class="field-error"><?= e($errors['role'] ?? '') ?></span>

        <button class="btn" type="submit">Register</button>
    </form>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
