<?php
// Model for user signups (volunteer/attend) on events

require_once __DIR__ . '/../db.php';

class EventVolunteer
{
    public static function join(int $eventId, int $userId): bool
    {
        $stmt = db()->prepare("
            INSERT IGNORE INTO event_volunteers (event_id, user_id)
            VALUES (?, ?)
        ");
        $stmt->bind_param('ii', $eventId, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function countForEvent(int $eventId): int
    {
        $stmt = db()->prepare("SELECT COUNT(*) AS c FROM event_volunteers WHERE event_id = ?");
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$res['c'];
    }

    public static function listForEvent(int $eventId): array
    {
        $stmt = db()->prepare("
            SELECT ev.*, u.name, u.email
            FROM event_volunteers ev
            JOIN users u ON ev.user_id = u.id
            WHERE ev.event_id = ?
            ORDER BY ev.created_at DESC
        ");
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
}
