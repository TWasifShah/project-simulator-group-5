<?php
class AuthController
{
    public function showRegister(): void
    {
        $title = 'Register';
        $errors = [];
        require app_root() . '/views/auth/register.php';
    }

    public function register(): void
    {
        require_csrf_or_fail();
        $name = trim_input('name');
        $email = trim_input('email');
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $role = trim_input('role');
        $errors = [];

        if ($name === '') $errors['name'] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
        if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
        if ($password !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';
        if (!in_array($role, ['admin', 'member'], true)) $errors['role'] = 'Select a valid role.';
        if ($email && User::emailExists($email)) $errors['email'] = 'This email is already registered.';

        if ($errors) {
            $title = 'Register';
            require app_root() . '/views/auth/register.php';
            return;
        }

        User::create($name, $email, password_hash($password, PASSWORD_DEFAULT), $role);
        flash('success', 'Registration successful. Please login.');
        redirect('login');
    }

    public function showLogin(): void
    {
        $title = 'Login';
        $errors = [];
        require app_root() . '/views/auth/login.php';
    }

    public function login(): void
    {
        require_csrf_or_fail();
        $email = trim_input('email');
        $password = (string)($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
        if ($password === '') $errors['password'] = 'Password is required.';

        $user = User::findByEmail($email);
        if (!$errors && (!$user || !password_verify($password, $user['password_hash']))) {
            $errors['login'] = 'Invalid email or password.';
        }

        if ($errors) {
            $title = 'Login';
            require app_root() . '/views/auth/login.php';
            return;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($remember) {
            $plainToken = bin2hex(random_bytes(32));
            User::setRememberToken((int)$user['id'], hash('sha256', $plainToken));
            set_remember_cookie((int)$user['id'], $plainToken);
        }

        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect($user['role'] === 'admin' ? 'admin/dashboard' : 'home');
    }

    public function profile(): void
    {
        require_login();
        $user = User::findById(current_user_id());
        $title = 'Profile';
        $errors = [];
        require app_root() . '/views/auth/profile.php';
    }

    public function updateProfile(): void
    {
        require_login();
        require_csrf_or_fail();
        $user = User::findById(current_user_id());
        $name = trim_input('name');
        $email = trim_input('email');
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');
        $errors = [];

        if ($name === '') $errors['name'] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
        if ($email && User::emailExists($email, (int)$user['id'])) $errors['email'] = 'This email is already used.';

        if ($newPassword !== '' || $currentPassword !== '' || $confirmPassword !== '') {
            if (!password_verify($currentPassword, $user['password_hash'])) $errors['current_password'] = 'Current password is incorrect.';
            if (strlen($newPassword) < 8) $errors['new_password'] = 'New password must be at least 8 characters.';
            if ($newPassword !== $confirmPassword) $errors['confirm_password'] = 'New passwords do not match.';
        }

        $profilePicture = null;
        try {
            $profilePicture = upload_image('profile_picture', 'profile');
        } catch (RuntimeException $e) {
            $errors['profile_picture'] = $e->getMessage();
        }

        if ($errors) {
            $title = 'Profile';
            require app_root() . '/views/auth/profile.php';
            return;
        }

        User::updateProfile((int)$user['id'], $name, $email, $profilePicture);
        if ($newPassword !== '') {
            User::updatePassword((int)$user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
        }
        $_SESSION['name'] = $name;
        flash('success', 'Profile updated successfully.');
        redirect('profile');
    }

    public function logout(): void
    {
        if (is_logged_in()) {
            User::setRememberToken(current_user_id(), null);
        }
        clear_remember_cookie();
        $_SESSION = [];
        session_destroy();
        redirect('home');
    }
}
