<?php
class Restaurant
{
    public static function all(): array
    {
        $result = db()->query('SELECT * FROM restaurants ORDER BY id DESC');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function create(string $name, string $location, string $area, string $shortBackground, string $goals): int
    {
        $stmt = db()->prepare('INSERT INTO restaurants (name, location, area, short_background, goals) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $name, $location, $area, $shortBackground, $goals);
        $stmt->execute();
        return db()->insert_id;
    }

    public static function update(int $id, string $name, string $location, string $area, string $shortBackground, string $goals): bool
    {
        $stmt = db()->prepare('UPDATE restaurants SET name = ?, location = ?, area = ?, short_background = ?, goals = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $name, $location, $area, $shortBackground, $goals, $id);
        return $stmt->execute();
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM restaurants WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public static function search(string $q = '', string $location = '', string $area = ''): array
    {
        $like = '%' . $q . '%';
        $loc = '%' . $location . '%';
        $ar = '%' . $area . '%';
        $stmt = db()->prepare("SELECT DISTINCT r.* FROM restaurants r
            LEFT JOIN menu_items m ON m.restaurant_id = r.id
            WHERE (? = '' OR r.name LIKE ? OR m.name LIKE ? OR m.description LIKE ?)
            AND (? = '' OR r.location LIKE ?)
            AND (? = '' OR r.area LIKE ?)
            ORDER BY r.id DESC");
        $stmt->bind_param('ssssssss', $q, $like, $like, $like, $location, $loc, $area, $ar);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function countAll(): int
    {
        $row = db()->query('SELECT COUNT(*) AS total FROM restaurants')->fetch_assoc();
        return (int)$row['total'];
    }
}
