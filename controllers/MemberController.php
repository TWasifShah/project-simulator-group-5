<?php
class MemberController
{
    public function search(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $location = trim((string)($_GET['location'] ?? ''));
        $area = trim((string)($_GET['area'] ?? ''));
        $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

        $restaurants = Restaurant::search($q, $location, $area);
        $items = MenuItem::search($q, $location, $area, $minPrice, $maxPrice);

        json_response(true, 'Search complete.', [
            'restaurants' => array_map([$this, 'restaurantCardData'], $restaurants),
            'items' => array_map([$this, 'itemCardData'], $items)
        ]);
    }

    private function restaurantCardData(array $r): array
    {
        return [
            'id' => (int)$r['id'],
            'name' => e($r['name']),
            'location' => e($r['location']),
            'area' => e($r['area']),
            'short_background' => e($r['short_background']),
            'url' => url('restaurant', ['id' => $r['id']])
        ];
    }

    private function itemCardData(array $i): array
    {
        return [
            'id' => (int)$i['id'],
            'name' => e($i['name']),
            'restaurant_name' => e($i['restaurant_name']),
            'location' => e($i['location']),
            'area' => e($i['area']),
            'price' => number_format((float)$i['price'], 2),
            'description' => e(mb_substr($i['description'], 0, 120)),
            'image_path' => $i['image_path'] ? e($i['image_path']) : '',
            'url' => url('menu-item', ['id' => $i['id']])
        ];
    }

    public function addReview(): void
    {
        require_member();
        require_csrf_or_fail();
        $menuItemId = (int)($_POST['menu_item_id'] ?? 0);
        $comment = trim((string)($_POST['comment'] ?? ''));
        if (!MenuItem::find($menuItemId)) {
            json_response(false, 'Menu item not found.', [], 404);
        }
        if ($comment === '' || mb_strlen($comment) > 500) {
            json_response(false, 'Comment is required and must be within 500 characters.', [], 422);
        }
        $reviewId = Review::add($menuItemId, current_user_id(), $comment);
        json_response(true, 'Review posted.', [
            'review' => [
                'id' => $reviewId,
                'user_name' => e($_SESSION['name']),
                'comment' => e($comment),
                'created_at' => date('Y-m-d H:i:s'),
                'can_delete' => true
            ]
        ]);
    }

    public function deleteReview(): void
    {
        require_login();
        require_csrf_or_fail();
        $id = (int)($_POST['id'] ?? 0);
        if (is_admin()) {
            Review::delete($id);
            json_response(true, 'Review deleted by admin.');
        }
        $ok = Review::deleteOwn($id, current_user_id());
        if (!$ok) json_response(false, 'You can delete only your own review.', [], 403);
        json_response(true, 'Review deleted.');
    }

    public function addRestaurantReview(): void
    {
        require_member();
        require_csrf_or_fail();
        $restaurantId = (int)($_POST['restaurant_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim((string)($_POST['comment'] ?? ''));
        if (!Restaurant::find($restaurantId)) json_response(false, 'Restaurant not found.', [], 404);
        if ($rating < 1 || $rating > 5) json_response(false, 'Rating must be between 1 and 5.', [], 422);
        if ($comment === '' || mb_strlen($comment) > 500) json_response(false, 'Comment is required and must be within 500 characters.', [], 422);
        $id = Review::addRestaurantReview($restaurantId, current_user_id(), $rating, $comment);
        json_response(true, 'Restaurant review posted.', [
            'review' => [
                'id' => $id,
                'user_name' => e($_SESSION['name']),
                'rating' => $rating,
                'comment' => e($comment),
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
