<?php
class HomeController
{
    public function home(): void
    {
        $title = 'Home';
        $restaurants = array_slice(Restaurant::all(), 0, 6);
        $menuItems = array_slice(MenuItem::all(), 0, 6);
        require app_root() . '/views/home.php';
    }

    public function restaurants(): void
    {
        $title = 'Browse Restaurants';
        $restaurants = Restaurant::all();
        require app_root() . '/views/browse/restaurant_list.php';
    }

    public function restaurant(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            flash('error', 'Restaurant not found.');
            redirect('browse');
        }
        $menuItems = MenuItem::byRestaurant($id);
        $restaurantReviews = Review::restaurantReviews($id);
        $title = $restaurant['name'];
        require app_root() . '/views/browse/restaurant_details.php';
    }

    public function menuItem(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = MenuItem::findWithRestaurant($id);
        if (!$item) {
            flash('error', 'Menu item not found.');
            redirect('browse');
        }
        $reviews = Review::forMenuItem($id);
        $title = $item['name'];
        require app_root() . '/views/browse/menu_item_details.php';
    }
}
