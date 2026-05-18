<?php
class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        if ($exceptId) {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
            $stmt->bind_param('si', $email, $exceptId);
        } else {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
        }
        $stmt->execute();
        return (bool)$stmt->get_result()->fetch_assoc();
    }

    public static function create(string $name, string $email, string $passwordHash, string $role): int
    {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $passwordHash, $role);
        $stmt->execute();
        return db()->insert_id;
    }

    public static function updateProfile(int $id, string $name, string $email, ?string $profilePicture): bool
    {
        if ($profilePicture) {
            $stmt = db()->prepare('UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?');
            $stmt->bind_param('sssi', $name, $email, $profilePicture, $id);
        } else {
            $stmt = db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssi', $name, $email, $id);
        }
        return $stmt->execute();
    }

    public static function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->bind_param('si', $passwordHash, $id);
        return $stmt->execute();
    }

    public static function setRememberToken(int $id, ?string $hashedToken): bool
    {
        $stmt = db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
        $stmt->bind_param('si', $hashedToken, $id);
        return $stmt->execute();
    }

    public static function allMembers(): array
    {
        $result = db()->query("SELECT id, name, email, profile_picture, created_at FROM users WHERE role = 'member' ORDER BY id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function deleteMember(int $id): bool
    {
        $stmt = db()->prepare("DELETE FROM users WHERE id = ? AND role = 'member'");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public static function countAll(): int
    {
        $row = db()->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc();
        return (int)$row['total'];
    }
}
