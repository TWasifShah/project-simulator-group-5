<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Restaurant.php';
require_once __DIR__ . '/models/MenuItem.php';
require_once __DIR__ . '/models/Review.php';
require_once __DIR__ . '/models/FoodExperience.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/MemberController.php';
require_once __DIR__ . '/controllers/FoodExpController.php';

db();
auto_login_from_cookie();

$route = $_GET['route'] ?? 'home';
$method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();
$home = new HomeController();
$admin = new AdminController();
$member = new MemberController();
$food = new FoodExpController();

try {
    switch ($route) {
        case 'home': $home->home(); break;
        case 'browse': $home->restaurants(); break;
        case 'restaurant': $home->restaurant(); break;
        case 'menu-item': $home->menuItem(); break;

        case 'register': $method === 'POST' ? $auth->register() : $auth->showRegister(); break;
        case 'login': $method === 'POST' ? $auth->login() : $auth->showLogin(); break;
        case 'profile': $method === 'POST' ? $auth->updateProfile() : $auth->profile(); break;
        case 'logout': $auth->logout(); break;

        case 'admin/dashboard': $admin->dashboard(); break;
        case 'admin/restaurants': $admin->restaurants(); break;
        case 'admin/restaurants/create': $method === 'POST' ? $admin->storeRestaurant() : $admin->createRestaurant(); break;
        case 'admin/restaurants/edit': $method === 'POST' ? $admin->updateRestaurant() : $admin->editRestaurant(); break;
        case 'admin/restaurants/delete': $admin->deleteRestaurant(); break;
        case 'admin/menu-items': $admin->menuItems(); break;
        case 'admin/menu-items/create': $method === 'POST' ? $admin->storeMenuItem() : $admin->createMenuItem(); break;
        case 'admin/menu-items/edit': $method === 'POST' ? $admin->updateMenuItem() : $admin->editMenuItem(); break;
        case 'admin/menu-items/delete': $admin->deleteMenuItem(); break;
        case 'admin/members': $admin->members(); break;
        case 'admin/members/delete': $admin->deleteMember(); break;
        case 'admin/reviews': $admin->reviews(); break;
        case 'admin/reviews/delete': $admin->deleteReview(); break;
        case 'admin/food-moderation': $admin->foodModeration(); break;
        case 'admin/food-posts/delete': $admin->deleteFoodPost(); break;
        case 'admin/food-comments/delete': $admin->deleteFoodComment(); break;

        case 'food-experience': $food->list(); break;
        case 'food-experience/details': $food->details(); break;
        case 'food-experience/create': $method === 'POST' ? $food->store() : $food->create(); break;
        case 'food-experience/edit': $method === 'POST' ? $food->update() : $food->edit(); break;
        case 'food-experience/delete': $food->delete(); break;

        case 'api/search': $member->search(); break;
        case 'api/reviews/add': $member->addReview(); break;
        case 'api/reviews/delete': $member->deleteReview(); break;
        case 'api/restaurant-reviews/add': $member->addRestaurantReview(); break;
        case 'api/food-exp/comments/add': $food->addComment(); break;
        case 'api/food-exp/comments/delete': $food->deleteComment(); break;

        default:
            http_response_code(404);
            $title = 'Page Not Found';
            require app_root() . '/views/layout/header.php';
            echo '<section class="card"><h1>404</h1><p>Page not found.</p><a class="btn" href="' . e(url('home')) . '">Go Home</a></section>';
            require app_root() . '/views/layout/footer.php';
    }
} catch (Throwable $e) {
    if (is_ajax_request()) {
        json_response(false, 'Server error: ' . $e->getMessage(), [], 500);
    }
    http_response_code(500);
    $title = 'Server Error';
    require app_root() . '/views/layout/header.php';
    echo '<section class="card"><h1>Server Error</h1><p>' . e($e->getMessage()) . '</p></section>';
    require app_root() . '/views/layout/footer.php';
}
