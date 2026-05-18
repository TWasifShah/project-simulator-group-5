<?php
class FoodExperience
{
    public static function posts(): array
    {
        $result = db()->query("SELECT p.*, u.name AS author_name, r.name AS restaurant_name, m.name AS menu_item_name
            FROM food_experience_posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN restaurants r ON r.id = p.restaurant_id
            LEFT JOIN menu_items m ON m.id = p.menu_item_id
            ORDER BY p.id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function findPost(int $id): ?array
    {
        $stmt = db()->prepare("SELECT p.*, u.name AS author_name, r.name AS restaurant_name, m.name AS menu_item_name
            FROM food_experience_posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN restaurants r ON r.id = p.restaurant_id
            LEFT JOIN menu_items m ON m.id = p.menu_item_id
            WHERE p.id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function createPost(int $userId, string $title, string $content, string $postType, ?int $restaurantId, ?int $menuItemId): int
    {
        $stmt = db()->prepare('INSERT INTO food_experience_posts (user_id, title, content, post_type, restaurant_id, menu_item_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssii', $userId, $title, $content, $postType, $restaurantId, $menuItemId);
        $stmt->execute();
        return db()->insert_id;
    }

    public static function updatePost(int $id, int $userId, string $title, string $content, string $postType, ?int $restaurantId, ?int $menuItemId): bool
    {
        $stmt = db()->prepare('UPDATE food_experience_posts SET title = ?, content = ?, post_type = ?, restaurant_id = ?, menu_item_id = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('sssiiii', $title, $content, $postType, $restaurantId, $menuItemId, $id, $userId);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }

    public static function deletePost(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM food_experience_posts WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public static function deleteOwnPost(int $id, int $userId): bool
    {
        $stmt = db()->prepare('DELETE FROM food_experience_posts WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public static function comments(int $postId): array
    {
        $stmt = db()->prepare('SELECT c.*, u.name AS user_name FROM food_experience_comments c JOIN users u ON u.id = c.user_id WHERE c.post_id = ? ORDER BY c.id ASC');
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function addComment(int $postId, int $userId, string $comment): int
    {
        $stmt = db()->prepare('INSERT INTO food_experience_comments (post_id, user_id, comment) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $postId, $userId, $comment);
        $stmt->execute();
        return db()->insert_id;
    }

    public static function findComment(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM food_experience_comments WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function deleteComment(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM food_experience_comments WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public static function deleteOwnComment(int $id, int $userId): bool
    {
        $stmt = db()->prepare('DELETE FROM food_experience_comments WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public static function countPosts(): int
    {
        $row = db()->query('SELECT COUNT(*) AS total FROM food_experience_posts')->fetch_assoc();
        return (int)$row['total'];
    }

    public static function allComments(): array
    {
        $result = db()->query('SELECT c.*, u.name AS user_name, p.title AS post_title FROM food_experience_comments c JOIN users u ON u.id = c.user_id JOIN food_experience_posts p ON p.id = c.post_id ORDER BY c.id DESC');
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
