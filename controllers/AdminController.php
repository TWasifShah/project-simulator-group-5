<?php
class AdminController
{
    private function gate(): void
    {
        require_admin();
    }

    public function dashboard(): void
    {
        $this->gate();
        $title = 'Admin Dashboard';
        $counts = [
            'restaurants' => Restaurant::countAll(),
            'menu_items' => MenuItem::countAll(),
            'reviews' => Review::countAll(),
            'food_posts' => FoodExperience::countPosts(),
            'users' => User::countAll()
        ];
        require app_root() . '/views/admin/dashboard.php';
    }

    public function restaurants(): void
    {
        $this->gate();
        $title = 'Manage Restaurants';
        $restaurants = Restaurant::all();
        require app_root() . '/views/admin/restaurants/list.php';
    }

    public function createRestaurant(): void
    {
        $this->gate();
        $title = 'Create Restaurant';
        $errors = [];
        require app_root() . '/views/admin/restaurants/create.php';
    }

    public function storeRestaurant(): void
    {
        $this->gate();
        require_csrf_or_fail();
        [$errors, $data] = $this->validateRestaurant();
        if ($errors) {
            $title = 'Create Restaurant';
            require app_root() . '/views/admin/restaurants/create.php';
            return;
        }
        Restaurant::create($data['name'], $data['location'], $data['area'], $data['short_background'], $data['goals']);
        flash('success', 'Restaurant created successfully.');
        redirect('admin/restaurants');
    }

    public function editRestaurant(): void
    {
        $this->gate();
        $restaurant = Restaurant::find((int)($_GET['id'] ?? 0));
        if (!$restaurant) redirect('admin/restaurants');
        $title = 'Edit Restaurant';
        $errors = [];
        require app_root() . '/views/admin/restaurants/edit.php';
    }

    public function updateRestaurant(): void
    {
        $this->gate();
        require_csrf_or_fail();
        $id = (int)($_POST['id'] ?? 0);
        $restaurant = Restaurant::find($id);
        if (!$restaurant) redirect('admin/restaurants');
        [$errors, $data] = $this->validateRestaurant();
        if ($errors) {
            $title = 'Edit Restaurant';
            require app_root() . '/views/admin/restaurants/edit.php';
            return;
        }
        Restaurant::update($id, $data['name'], $data['location'], $data['area'], $data['short_background'], $data['goals']);
        flash('success', 'Restaurant updated successfully.');
        redirect('admin/restaurants');
    }

    public function deleteRestaurant(): void
    {
        $this->gate();
        require_csrf_or_fail();
        Restaurant::delete((int)($_POST['id'] ?? 0));
        flash('success', 'Restaurant and its menu items deleted.');
        redirect('admin/restaurants');
    }

    private function validateRestaurant(): array
    {
        $data = [
            'name' => trim_input('name'),
            'location' => trim_input('location'),
            'area' => trim_input('area'),
            'short_background' => trim_input('short_background'),
            'goals' => trim_input('goals')
        ];
        $errors = [];
        foreach ($data as $key => $value) {
            if ($value === '') $errors[$key] = 'This field is required.';
        }
        return [$errors, $data];
    }

    public function menuItems(): void
    {
        $this->gate();
        $title = 'Manage Menu Items';
        $restaurantId = (int)($_GET['restaurant_id'] ?? 0);
        $restaurant = $restaurantId ? Restaurant::find($restaurantId) : null;
        $items = $restaurant ? MenuItem::byRestaurant($restaurantId) : MenuItem::all();
        require app_root() . '/views/admin/menu_items/list.php';
    }

    public function createMenuItem(): void
    {
        $this->gate();
        $title = 'Create Menu Item';
        $errors = [];
        $restaurants = Restaurant::all();
        require app_root() . '/views/admin/menu_items/create.php';
    }

    public function storeMenuItem(): void
    {
        $this->gate();
        require_csrf_or_fail();
        $restaurants = Restaurant::all();
        [$errors, $data] = $this->validateMenuItem(true);
        if ($errors) {
            $title = 'Create Menu Item';
            require app_root() . '/views/admin/menu_items/create.php';
            return;
        }
        MenuItem::create($data['restaurant_id'], $data['name'], $data['description'], $data['price'], $data['image_path']);
        flash('success', 'Menu item created successfully.');
        redirect('admin/menu-items');
    }

    public function editMenuItem(): void
    {
        $this->gate();
        $item = MenuItem::find((int)($_GET['id'] ?? 0));
        if (!$item) redirect('admin/menu-items');
        $title = 'Edit Menu Item';
        $errors = [];
        $restaurants = Restaurant::all();
        require app_root() . '/views/admin/menu_items/edit.php';
    }

    public function updateMenuItem(): void
    {
        $this->gate();
        require_csrf_or_fail();
        $item = MenuItem::find((int)($_POST['id'] ?? 0));
        if (!$item) redirect('admin/menu-items');
        $restaurants = Restaurant::all();
        [$errors, $data] = $this->validateMenuItem(false);
        if ($errors) {
            $title = 'Edit Menu Item';
            require app_root() . '/views/admin/menu_items/edit.php';
            return;
        }
        MenuItem::update((int)$item['id'], $data['restaurant_id'], $data['name'], $data['description'], $data['price'], $data['image_path']);
        flash('success', 'Menu item updated successfully.');
        redirect('admin/menu-items');
    }

    public function deleteMenuItem(): void
    {
        $this->gate();
        require_csrf_or_fail();
        MenuItem::delete((int)($_POST['id'] ?? 0));
        flash('success', 'Menu item deleted successfully.');
        redirect('admin/menu-items');
    }

    private function validateMenuItem(bool $imageRequired): array
    {
        $data = [
            'restaurant_id' => (int)($_POST['restaurant_id'] ?? 0),
            'name' => trim_input('name'),
            'description' => trim_input('description'),
            'price' => (float)($_POST['price'] ?? 0),
            'image_path' => null
        ];
        $errors = [];
        if (!Restaurant::find($data['restaurant_id'])) $errors['restaurant_id'] = 'Select a valid restaurant.';
        if ($data['name'] === '') $errors['name'] = 'Name is required.';
        if ($data['description'] === '') $errors['description'] = 'Description is required.';
        if ($data['price'] <= 0) $errors['price'] = 'Price must be greater than 0.';
        if ($imageRequired && empty($_FILES['image']['name'])) $errors['image'] = 'Image is required.';
        try {
            $data['image_path'] = upload_image('image', 'menu');
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
        return [$errors, $data];
    }

    public function members(): void
    {
        $this->gate();
        $title = 'Manage Members';
        $members = User::allMembers();
        require app_root() . '/views/admin/members.php';
    }

    public function deleteMember(): void
    {
        $this->gate();
        require_csrf_or_fail();
        User::deleteMember((int)($_POST['id'] ?? 0));
        flash('success', 'Member and related content deleted.');
        redirect('admin/members');
    }

    public function reviews(): void
    {
        $this->gate();
        $title = 'Moderate Food Item Reviews';
        $reviews = Review::all();
        require app_root() . '/views/admin/reviews.php';
    }

    public function deleteReview(): void
    {
        $this->gate();
        require_csrf_or_fail();
        Review::delete((int)($_POST['id'] ?? 0));
        flash('success', 'Review removed.');
        redirect('admin/reviews');
    }

    public function foodModeration(): void
    {
        $this->gate();
        $title = 'Food Experience Moderation';
        $posts = FoodExperience::posts();
        $comments = FoodExperience::allComments();
        require app_root() . '/views/admin/food_moderation.php';
    }

    public function deleteFoodPost(): void
    {
        $this->gate();
        require_csrf_or_fail();
        FoodExperience::deletePost((int)($_POST['id'] ?? 0));
        flash('success', 'Food experience post removed.');
        redirect('admin/food-moderation');
    }

    public function deleteFoodComment(): void
    {
        $this->gate();
        require_csrf_or_fail();
        FoodExperience::deleteComment((int)($_POST['id'] ?? 0));
        flash('success', 'Food experience comment removed.');
        redirect('admin/food-moderation');
    }
}
