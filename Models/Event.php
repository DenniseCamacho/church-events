<?php
// Model for interacting with the 'events' table in the database

require_once __DIR__ . '/../db.php';

class Event
{
    public ?int $id = null;
    public int $church_id = 0;
    public string $title = '';
    public ?string $start_datetime = null;
    public ?string $end_datetime = null;
    public ?string $location_override = null;
    public string $status = 'pending';
    public ?int $duplicate_of_event_id = null;
    public ?int $created_by_user_id = null;
    public ?string $created_at = null;

    // fill object from array
    public function fill(array $data): void
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    // insert new record
    public function save(): bool
    {
        if ($this->id !== null) {
            return false;
        }

        $stmt = db()->prepare("
            INSERT INTO events 
                (church_id, title, start_datetime, end_datetime, location_override, status, duplicate_of_event_id, created_by_user_id)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $dup = $this->duplicate_of_event_id ?: null;
        $end = $this->end_datetime ?: null;
        $loc = $this->location_override ?: null;

        $stmt->bind_param(
            'isssssii',
            $this->church_id,
            $this->title,
            $this->start_datetime,
            $end,
            $loc,
            $this->status,
            $dup,
            $this->created_by_user_id
        );
        $ok = $stmt->execute();
        if ($ok) {
            $this->id = db()->insert_id;
        }
        $stmt->close();
        return $ok;
    }
    // LIST ALL EVENTS FOR ADMIN WITH COUNTS

    public static function allWithCounts(): array
    {
        $sql = "
            SELECT e.*, c.name AS church_name, u.name AS creator_name,
                   (SELECT COUNT(*) FROM event_volunteers ev WHERE ev.event_id = e.id) AS volunteer_count
            FROM events e
            JOIN churches c ON e.church_id = c.id
            LEFT JOIN users u ON e.created_by_user_id = u.id
            ORDER BY e.start_datetime DESC
        ";
        $res = db()->query($sql);
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }

    // LIST APPROVED EVENTS FOR VOLUNTEERS

    public static function allApprovedWithCounts(): array
    {
        $sql = "
            SELECT e.*, c.name AS church_name,
                   (SELECT COUNT(*) FROM event_volunteers ev WHERE ev.event_id = e.id) AS volunteer_count
            FROM events e
            JOIN churches c ON e.church_id = c.id
            WHERE e.status = 'approved'
            ORDER BY e.start_datetime DESC
        ";
        $res = db()->query($sql);
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }

    // LIST PENDING EVENTS FOR ADMIN APPROVAL

    public static function pending(): array
    {
        $sql = "
            SELECT e.*, c.name AS church_name, u.name AS creator_name
            FROM events e
            JOIN churches c ON e.church_id = c.id
            LEFT JOIN users u ON e.created_by_user_id = u.id
            WHERE e.status = 'pending'
            ORDER BY e.start_datetime DESC
        ";
        $res = db()->query($sql);
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }
    // get single event with church and creator
    public static function findWithChurch(int $id): ?array
    {
        $stmt = db()->prepare("
        SELECT e.*, c.name AS church_name
        FROM events e
        JOIN churches c ON e.church_id = c.id
        WHERE e.id = ?
        LIMIT 1
    ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }


    // LIST EVENTS BY CREATOR (ORGANIZER) WITH COUNTS

    public static function byCreatorWithCounts(int $userId): array
    {
        $sql = "
            SELECT e.*, c.name AS church_name,
                   (SELECT COUNT(*) FROM event_volunteers ev WHERE ev.event_id = e.id) AS volunteer_count
            FROM events e
            JOIN churches c ON e.church_id = c.id
            WHERE e.created_by_user_id = ?
            ORDER BY e.start_datetime DESC
        ";
        $stmt = db()->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    // APPROVE EVENT

    public static function approve(int $id): bool
    {
        $stmt = db()->prepare("UPDATE events SET status = 'approved' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // REJECT EVENT

    public static function reject(int $id): bool
    {
        $stmt = db()->prepare("UPDATE events SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    // get all events for admin
    public static function all(): array
    {
        $sql = "
            SELECT e.*, c.name AS church_name, u.name AS creator_name
            FROM events e
            JOIN churches c ON e.church_id = c.id
            LEFT JOIN users u ON e.created_by_user_id = u.id
            ORDER BY e.start_datetime DESC
        ";
        $res = db()->query($sql);
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }

    // get events by creator for organizer
    public static function byCreator(int $userId): array
    {
        $sql = "
            SELECT e.*, c.name AS church_name
            FROM events e
            JOIN churches c ON e.church_id = c.id
            WHERE e.created_by_user_id = ?
            ORDER BY e.start_datetime DESC
        ";
        $stmt = db()->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }
}
