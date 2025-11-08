<?php
// PURPOSE: Model for storing and retrieving volunteer sign-ups for events

require_once __DIR__ . '/../db.php';

class EventVolunteer
{
    // user joins an event
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

    // user withdraws from an event
    public static function withdraw(int $eventId, int $userId): bool
    {
        $stmt = db()->prepare("
            DELETE FROM event_volunteers
            WHERE event_id = ? AND user_id = ?
        ");
        $stmt->bind_param('ii', $eventId, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // count signups for one event
    public static function countForEvent(int $eventId): int
    {
        $stmt = db()->prepare("SELECT COUNT(*) AS c FROM event_volunteers WHERE event_id = ?");
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$res['c'];
    }

    // list signups for one event (for organizer/admin)
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

    // list signups for one user (for volunteer dashboard)
    public static function listForUser(int $userId): array
    {
        $stmt = db()->prepare("
            SELECT ev.*, e.title, e.start_datetime, c.name AS church_name
            FROM event_volunteers ev
            JOIN events e ON ev.event_id = e.id
            JOIN churches c ON e.church_id = c.id
            WHERE ev.user_id = ?
            ORDER BY e.start_datetime DESC
        ");
        $stmt->bind_param('i', $userId);
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
