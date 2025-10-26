<?php
// Each Church object represents one row in the 'churches' table.
// The object can load itself from the database, update itself, create new entries,
// and delete itself safely using prepared statements.

require_once __DIR__ . '/../db.php';

class Church
{
    // Define properties to match database columns
    public ?int $id = null;
    public string $name = '';
    public string $slug = '';
    public string $address1 = '';
    public ?string $address2 = null;
    public string $city = '';
    public string $state = '';
    public string $postal_code = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public bool $is_active = true;
    public ?string $created_at = null;

    // Load a church from the database by ID
    public static function find(int $id): ?Church
    {
        $stmt = db()->prepare("SELECT * FROM churches WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        // If no match found, return null
        if (!$row) return null;

        // Create a new Church object and populate it with the row data
        $church = new self();
        $church->fill($row);
        return $church;
    }

    // Create a new Church object from an associative array
    // (used internally and can also be useful for forms)
    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            // Only assign properties that exist in the class
            if (property_exists($this, $key)) {
                // Type cast booleans and floats for safety
                if ($key === 'is_active') {
                    $this->$key = (bool)$value;
                } elseif (in_array($key, ['latitude', 'longitude']) && $value !== null) {
                    $this->$key = (float)$value;
                } else {
                    $this->$key = $value;
                }
            }
        }
    }

    // Save the current object (create or update)
    public function save(): bool
    {
        // Automatically create a slug before saving
        $this->slug = $this->makeSlug($this->name, $this->city, $this->state);

        // If the record already exists, perform an UPDATE
        if ($this->id !== null) {
            $stmt = db()->prepare("
                UPDATE churches
                SET name=?, slug=?, address1=?, address2=?, city=?, state=?, postal_code=?, latitude=?, longitude=?, is_active=?
                WHERE id=?
            ");
            $stmt->bind_param(
                'ssssssddiii',
                $this->name,
                $this->slug,
                $this->address1,
                $this->address2,
                $this->city,
                $this->state,
                $this->postal_code,
                $this->latitude,
                $this->longitude,
                $this->is_active,
                $this->id
            );
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }

        // Otherwise perform an INSERT for a new record
        $stmt = db()->prepare("
            INSERT INTO churches (name, slug, address1, address2, city, state, postal_code, latitude, longitude, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssssssdddi',
            $this->name,
            $this->slug,
            $this->address1,
            $this->address2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->latitude,
            $this->longitude,
            $this->is_active
        );
        $ok = $stmt->execute();
        if ($ok) {
            // Get the auto-generated ID of the new record
            $this->id = db()->insert_id;
        }
        $stmt->close();
        return $ok;
    }

    // Delete this church from the database
    public function delete(): bool
    {
        // Do nothing if it has not been saved yet
        if ($this->id === null) return false;
        $stmt = db()->prepare("DELETE FROM churches WHERE id = ?");
        $stmt->bind_param('i', $this->id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // Fetch all churches as objects instead of plain arrays
    public static function all(): array
    {
        $res = db()->query("SELECT * FROM churches ORDER BY name ASC");
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $church = new self();
            $church->fill($row);
            $list[] = $church;
        }
        return $list;
    }

    // Helper to check for duplicates (optional, can be used before save)
    public static function exists(string $name, string $city, string $state, string $postal): bool
    {
        $stmt = db()->prepare("SELECT COUNT(*) AS cnt FROM churches WHERE name=? AND city=? AND state=? AND postal_code=?");
        $stmt->bind_param('ssss', $name, $city, $state, $postal);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res['cnt'] > 0;
    }

    // Private helper to make URL-friendly slugs
    private function makeSlug(string $name, string $city, string $state): string
    {
        $base = strtolower(trim("$name $city $state"));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $base);
        $slug = trim($slug, '-');
        return substr($slug, 0, 180);
    }
}
