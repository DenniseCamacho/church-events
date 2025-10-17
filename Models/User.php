<?php
require_once __DIR__ . '/../db.php';

class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    public static function findByPublicId(string $publicId): ?array
    {
        $stmt = db()->prepare("SELECT * FROM users WHERE public_id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param('s', $publicId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    // NEW: Create user, then auto-generate a safe unique handle.
    public static function createAutoHandle(string $name, string $email, string $password, int $role_id = 3): ?array
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 1) insert without handle to get id
        $stmt = db()->prepare("INSERT INTO users (name, handle, email, password_hash, role_id) VALUES (?, 'pending', ?, ?, ?)");
        if (!$stmt) return null;
        $stmt->bind_param('sssi', $name, $email, $hash, $role_id);
        $ok = $stmt->execute();
        if (!$ok) {
            $stmt->close();
            return null;
        }
        $newId = db()->insert_id;
        $stmt->close();

        // 2) build deterministic, unique handle from id
        $handle = self::handleFromId($newId);

        // 3) update the row with the final handle
        $stmt2 = db()->prepare("UPDATE users SET handle = ? WHERE id = ?");
        if (!$stmt2) return null;
        $stmt2->bind_param('si', $handle, $newId);
        $ok2 = $stmt2->execute();
        $stmt2->close();
        if (!$ok2) return null;

        // 4) return the created user (id + public_id)
        $stmt3 = db()->prepare("SELECT id, public_id, name, handle, email, role_id FROM users WHERE id = ? LIMIT 1");
        if (!$stmt3) return null;
        $stmt3->bind_param('i', $newId);
        $stmt3->execute();
        $res = $stmt3->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt3->close();

        return $row ?: null;
    }

    // Safe handle generator: adjective-animal-base36(id)
    private static function handleFromId(int $id): string
    {
        $adjectives = ['bright', 'calm', 'kind', 'brave', 'hopeful', 'quiet', 'swift', 'steady', 'warm', 'humble', 'able', 'bold', 'clever', 'eager', 'fair', 'gentle', 'honest', 'merry', 'neat', 'polite'];
        $animals    = ['otter', 'sparrow', 'fox', 'robin', 'panda', 'falcon', 'lynx', 'bison', 'hare', 'dolphin', 'wren', 'owl', 'koala', 'yak', 'ibis', 'seal', 'moose', 'mink', 'eagle', 'heron'];

        $adj = $adjectives[$id % count($adjectives)];
        $ani = $animals[$id % count($animals)];
        $suffix = base_convert((string)$id, 10, 36); // short, unique per id

        return "{$adj}-{$ani}-{$suffix}";
    }
}
