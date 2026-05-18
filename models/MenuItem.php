<?php
class MenuItem
{
    public static function all(): array
    {
        $result = db()->query('SELECT m.*, r.name AS restaurant_name FROM menu_items m JOIN restaurants r ON r.id = m.restaurant_id ORDER BY m.id DESC');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM menu_items WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function findWithRestaurant(int $id): ?array
    {
        $stmt = db()->prepare('SELECT m.*, r.name AS restaurant_name, r.location, r.area FROM menu_items m JOIN restaurants r ON r.id = m.restaurant_id WHERE m.id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function byRestaurant(int $restaurantId): array
    {
        $stmt = db()->prepare('SELECT * FROM menu_items WHERE restaurant_id = ? ORDER BY id DESC');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function create(int $restaurantId, string $name, string $description, float $price, ?string $imagePath): int
    {
        $stmt = db()->prepare('INSERT INTO menu_items (restaurant_id, name, description, price, image_path) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issds', $restaurantId, $name, $description, $price, $imagePath);
        $stmt->execute();
        return db()->insert_id;
    }

    public static function update(int $id, int $restaurantId, string $name, string $description, float $price, ?string $imagePath): bool
    {
        if ($imagePath) {
            $stmt = db()->prepare('UPDATE menu_items SET restaurant_id = ?, name = ?, description = ?, price = ?, image_path = ? WHERE id = ?');
            $stmt->bind_param('issdsi', $restaurantId, $name, $description, $price, $imagePath, $id);
        } else {
            $stmt = db()->prepare('UPDATE menu_items SET restaurant_id = ?, name = ?, description = ?, price = ? WHERE id = ?');
            $stmt->bind_param('issdi', $restaurantId, $name, $description, $price, $id);
        }
        return $stmt->execute();
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public static function search(string $q = '', string $location = '', string $area = '', ?float $minPrice = null, ?float $maxPrice = null): array
    {
        $sql = "SELECT m.*, r.name AS restaurant_name, r.location, r.area FROM menu_items m
                JOIN restaurants r ON r.id = m.restaurant_id
                WHERE (? = '' OR m.name LIKE ? OR m.description LIKE ? OR r.name LIKE ?)
                AND (? = '' OR r.location LIKE ?)
                AND (? = '' OR r.area LIKE ?)";
        $params = [];
        $types = 'ssssssss';
        $like = '%' . $q . '%';
        $loc = '%' . $location . '%';
        $ar = '%' . $area . '%';
        $params = [$q, $like, $like, $like, $location, $loc, $area, $ar];
        if ($minPrice !== null) {
            $sql .= ' AND m.price >= ?';
            $types .= 'd';
            $params[] = $minPrice;
        }
        if ($maxPrice !== null) {
            $sql .= ' AND m.price <= ?';
            $types .= 'd';
            $params[] = $maxPrice;
        }
        $sql .= ' ORDER BY m.id DESC';
        $stmt = db()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function countAll(): int
    {
        $row = db()->query('SELECT COUNT(*) AS total FROM menu_items')->fetch_assoc();
        return (int)$row['total'];
    }
}
