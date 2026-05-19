<?php
/*
 | Online Food Blog - Database and shared helper functions
 | Change the DB constants below if your MySQL username/password is different.
 | This file creates the database and tables automatically. No .sql file is needed.
 */

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'online_food_blog_full';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    $server = new mysqli(DB_HOST, DB_USER, DB_PASS);
    $server->set_charset('utf8mb4');
    $server->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $server->select_db(DB_NAME);
    $conn = $server;
    ensure_schema($conn);
    return $conn;
}

function ensure_schema(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(160) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin','member') NOT NULL DEFAULT 'member',
        profile_picture VARCHAR(255) DEFAULT NULL,
        remember_token VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS restaurants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(160) NOT NULL,
        location VARCHAR(120) NOT NULL,
        area VARCHAR(120) NOT NULL,
        short_background TEXT NOT NULL,
        goals TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT NOT NULL,
        name VARCHAR(160) NOT NULL,
        description TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_menu_restaurant (restaurant_id),
        CONSTRAINT fk_menu_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_item_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_reviews_item (menu_item_id),
        INDEX idx_reviews_user (user_id),
        CONSTRAINT fk_reviews_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS restaurant_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT NOT NULL,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL DEFAULT 5,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_rreviews_restaurant (restaurant_id),
        INDEX idx_rreviews_user (user_id),
        CONSTRAINT fk_rreviews_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
        CONSTRAINT fk_rreviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS food_experience_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(180) NOT NULL,
        content TEXT NOT NULL,
        post_type ENUM('restaurant','food','both') NOT NULL DEFAULT 'both',
        restaurant_id INT DEFAULT NULL,
        menu_item_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_food_posts_user (user_id),
        INDEX idx_food_posts_restaurant (restaurant_id),
        INDEX idx_food_posts_menu (menu_item_id),
        CONSTRAINT fk_food_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_food_posts_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL,
        CONSTRAINT fk_food_posts_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS food_experience_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_food_comments_post (post_id),
        INDEX idx_food_comments_user (user_id),
        CONSTRAINT fk_food_comments_post FOREIGN KEY (post_id) REFERENCES food_experience_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_food_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    seed_data($conn);
}

function seed_data(mysqli $conn): void
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM users");
    $count = (int)$result->fetch_assoc()['total'];
    if ($count === 0) {
        $adminPass = password_hash('Admin12345', PASSWORD_DEFAULT);
        $memberPass = password_hash('Member12345', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $name = 'Admin User'; $email = 'admin@example.com'; $role = 'admin';
        $stmt->bind_param('ssss', $name, $email, $adminPass, $role);
        $stmt->execute();
        $name = 'Member User'; $email = 'member@example.com'; $role = 'member';
        $stmt->bind_param('ssss', $name, $email, $memberPass, $role);
        $stmt->execute();
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM restaurants");
    $count = (int)$result->fetch_assoc()['total'];
    if ($count === 0) {
        $restaurants = [
            ['Spice Garden', 'Dhaka', 'Dhanmondi', 'A friendly restaurant serving Bangladeshi and fusion meals.', 'Serve fresh food with consistent taste and warm hospitality.'],
            ['Urban Bites', 'Dhaka', 'Gulshan', 'A modern cafe for burgers, pasta, coffee, and snacks.', 'Make casual dining simple, clean, and affordable.'],
            ['Royal Kitchen', 'Chattogram', 'Agrabad', 'A family restaurant with rice platters and seafood dishes.', 'Promote local flavor with safe kitchen standards.']
        ];
        $stmt = $conn->prepare("INSERT INTO restaurants (name, location, area, short_background, goals) VALUES (?, ?, ?, ?, ?)");
        foreach ($restaurants as $r) {
            $stmt->bind_param('sssss', $r[0], $r[1], $r[2], $r[3], $r[4]);
            $stmt->execute();
        }

        $items = [
            [1, 'Chicken Biryani', 'Aromatic rice cooked with chicken, spices, and fried onion.', 250.00, null],
            [1, 'Beef Tehari', 'Traditional tehari with tender beef and balanced spice.', 220.00, null],
            [2, 'Classic Burger', 'Juicy burger with cheese, lettuce, and house sauce.', 180.00, null],
            [2, 'Creamy Pasta', 'Pasta served with creamy sauce and grilled chicken.', 260.00, null],
            [3, 'Seafood Platter', 'Mixed seafood platter with rice, salad, and sauce.', 520.00, null]
        ];
        $stmt = $conn->prepare("INSERT INTO menu_items (restaurant_id, name, description, price, image_path) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $i) {
            $stmt->bind_param('issds', $i[0], $i[1], $i[2], $i[3], $i[4]);
            $stmt->execute();
        }
    }
}

function app_root(): string
{
    return dirname(__DIR__);
}

function url(string $route = 'home', array $params = []): string
{
    $params = array_merge(['route' => $route], $params);
    return 'index.php?' . http_build_query($params);
}

function redirect(string $route, array $params = []): void
{
    header('Location: ' . url($route, $params));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf_or_fail(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verify_csrf($token)) {
        if (is_ajax_request()) {
            json_response(false, 'Invalid CSRF token.', [], 403);
        }
        $_SESSION['flash_error'] = 'Invalid CSRF token. Please try again.';
        redirect('home');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

function consume_flash(string $type): ?string
{
    $key = 'flash_' . $type;
    if (!empty($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function is_admin(): bool
{
    return current_role() === 'admin';
}

function is_member(): bool
{
    return current_role() === 'member';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please login first.');
        redirect('login');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        flash('error', 'Only admin can access that page.');
        redirect('home');
    }
}

function require_member(): void
{
    require_login();
    if (!is_member()) {
        if (is_ajax_request()) {
            json_response(false, 'Only members can perform this action.', [], 403);
        }
        flash('error', 'Only members can perform this action.');
        redirect('home');
    }
}

function is_ajax_request(): bool
{
    return str_starts_with($_GET['route'] ?? '', 'api/');
}

function json_response(bool $success, string $message = '', array $data = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function trim_input(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function upload_image(string $field, string $folder, array $allowedMimes = ['image/jpeg', 'image/png'], int $maxBytes = 2097152): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed.');
    }

    if ($_FILES[$field]['size'] > $maxBytes) {
        throw new RuntimeException('File size must be 2MB or less.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$field]['tmp_name']);
    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('Only JPEG and PNG images are allowed.');
    }

    $ext = $mime === 'image/png' ? 'png' : 'jpg';
    $safeName = bin2hex(random_bytes(12)) . '.' . $ext;
    $relativeDir = 'public/uploads/' . trim($folder, '/') . '/';
    $absoluteDir = app_root() . '/' . $relativeDir;
    if (!is_dir($absoluteDir)) {
        mkdir($absoluteDir, 0775, true);
    }
    $absolutePath = $absoluteDir . $safeName;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $absolutePath)) {
        throw new RuntimeException('Could not save uploaded image.');
    }

    return $relativeDir . $safeName;
}

function auto_login_from_cookie(): void
{
    if (is_logged_in() || empty($_COOKIE['remember_me'])) {
        return;
    }

    $parts = explode('|', $_COOKIE['remember_me']);
    if (count($parts) !== 2) {
        return;
    }

    [$userId, $plainToken] = $parts;
    if (!ctype_digit($userId) || $plainToken === '') {
        return;
    }

    require_once app_root() . '/models/User.php';
    $user = User::findById((int)$userId);
    if (!$user || empty($user['remember_token'])) {
        return;
    }

    $hashed = hash('sha256', $plainToken);
    if (hash_equals($user['remember_token'], $hashed)) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
    }
}

function set_remember_cookie(int $userId, string $plainToken): void
{
    setcookie('remember_me', $userId . '|' . $plainToken, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function clear_remember_cookie(): void
{
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
