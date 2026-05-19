<?php
class Review
{
    public static function forMenuItem(int $menuItemId): array
    {
        $stmt = db()->prepare('SELECT rv.*, u.name AS user_name FROM reviews rv JOIN users u ON u.id = rv.user_id WHERE rv.menu_item_id = ? ORDER BY rv.id DESC');
        $stmt->bind_param('i', $menuItemId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function add(int $menuItemId, int $userId, string $comment): int
    {
        $stmt = db()->prepare('INSERT INTO reviews (menu_item_id, user_id, comment) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $menuItemId, $userId, $comment);
        $stmt->execute();
        return db()->insert_id;
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM reviews WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM reviews WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public static function deleteOwn(int $id, int $userId): bool
    {
        $stmt = db()->prepare('DELETE FROM reviews WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public static function all(): array
    {
        $result = db()->query('SELECT rv.*, u.name AS user_name, m.name AS menu_item_name, r.name AS restaurant_name FROM reviews rv JOIN users u ON u.id = rv.user_id JOIN menu_items m ON m.id = rv.menu_item_id JOIN restaurants r ON r.id = m.restaurant_id ORDER BY rv.id DESC');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function countAll(): int
    {
        $row = db()->query('SELECT COUNT(*) AS total FROM reviews')->fetch_assoc();
        return (int)$row['total'];
    }

    public static function addRestaurantReview(int $restaurantId, int $userId, int $rating, string $comment): int
    {
        $stmt = db()->prepare('INSERT INTO restaurant_reviews (restaurant_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiis', $restaurantId, $userId, $rating, $comment);
        $stmt->execute();
        return db()->insert_id;
    }

    public static function restaurantReviews(int $restaurantId): array
    {
        $stmt = db()->prepare('SELECT rr.*, u.name AS user_name FROM restaurant_reviews rr JOIN users u ON u.id = rr.user_id WHERE rr.restaurant_id = ? ORDER BY rr.id DESC');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function deleteOwnRestaurantReview(int $id, int $userId): bool
    {
        $stmt = db()->prepare('DELETE FROM restaurant_reviews WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
