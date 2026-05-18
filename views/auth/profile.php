<?php require app_root() . '/views/layout/header.php'; ?>
<section class="card auth-card">
    <h1>My Profile</h1>
    <?php if (!empty($user['profile_picture'])): ?>
        <img class="profile-img" src="<?= e($user['profile_picture']) ?>" alt="Profile picture">
    <?php endif; ?>
    <form id="profileForm" method="post" action="<?= url('profile') ?>" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Name</label>
        <input type="text" name="name" value="<?= e($_POST['name'] ?? $user['name']) ?>" required>
        <span class="field-error"><?= e($errors['name'] ?? '') ?></span>

        <label>Email</label>
        <input type="email" name="email" value="<?= e($_POST['email'] ?? $user['email']) ?>" required>
        <span class="field-error"><?= e($errors['email'] ?? '') ?></span>

        <label>Profile Picture</label>
        <input type="file" name="profile_picture" accept="image/jpeg,image/png">
        <span class="field-error"><?= e($errors['profile_picture'] ?? '') ?></span>

        <h3>Change Password</h3>
        <p class="muted">Leave password fields blank if you do not want to change password.</p>
        <label>Current Password</label>
        <input type="password" name="current_password">
        <span class="field-error"><?= e($errors['current_password'] ?? '') ?></span>

        <label>New Password</label>
        <input type="password" name="new_password" minlength="8">
        <span class="field-error"><?= e($errors['new_password'] ?? '') ?></span>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" minlength="8">
        <span class="field-error"><?= e($errors['confirm_password'] ?? '') ?></span>

        <button class="btn" type="submit">Update Profile</button>
    </form>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
