<?php
// Controller for managing Church CRUD operations.
// Handles form submissions, validation, and view rendering for church records.
// Access rules:
// - Admin (role_id = 1): full create, update, delete
// - Organizer (role_id = 2): view list only
// - Volunteer (role_id = 3): no access

require_once __DIR__ . '/../Models/Church.php';
require_once __DIR__ . '/../helpers.php';

class ChurchController
{
    //ACCESS CONTROL
    // Allow only admin 
    private static function requireAdmin()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if ($role !== 1) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // Allow admin or organizer
    private static function requireAdminOrOrganizer()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if (!in_array($role, [1, 2], true)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // LIST CHURCHES
    // Displays list of all churches

    public static function index()
    {
        self::requireAdminOrOrganizer();

        $churches = Church::all();

        self::view('church/index', compact('churches'));
    }
    // CREATE NEW CHURCH
    // Displays the form for creating a new church
    // Only admin can access this form
    public static function createForm()
    {
        self::requireAdmin();
        self::view('church/create');
    }

    // Handles form submission for creating a new church
    // Only admin can create a new church
    public static function store()
    {
        self::requireAdmin();

        if (!verify_csrf()) {
            return self::view('church/create', ['error' => 'Invalid form token.']);
        }

        // Validate and sanitize input
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'address1' => trim($_POST['address1'] ?? ''),
            'address2' => trim($_POST['address2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => strtoupper(trim($_POST['state'] ?? '')),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'latitude' => null,
            'longitude' => null,
            'is_active' => 1
        ];

        // Basic server-side validation
        if (strlen($data['name']) < 2 || strlen($data['city']) < 1 || strlen($data['state']) != 2) {
            return self::view('church/create', ['error' => 'Invalid or missing fields.']);
        }

        // Duplicate check before creating
        if (Church::exists($data['name'], $data['city'], $data['state'], $data['postal_code'])) {
            return self::view('church/create', ['error' => 'A church with these details already exists.']);
        }

        // Create and save the church
        $church = new Church();
        $church->fill($data);
        $ok = $church->save();

        if (!$ok) {
            return self::view('church/create', ['error' => 'Error saving record. Please try again.']);
        }

        // Reload list with success message
        return self::view('church/index', [
            'success' => 'Church created successfully.',
            'churches' => Church::all()
        ]);
    }

    // EDIT EXISTING CHURCH
    // Displays edit form for an existing church
    // Only admin can edit
    public static function editForm()
    {
        self::requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $church = Church::find($id);

        if (!$church) {
            http_response_code(404);
            die('Church not found');
        }

        self::view('church/edit', compact('church'));
    }

    // Handles update submission for an existing church
    // Only admin can update
    public static function update()
    {
        self::requireAdmin();

        if (!verify_csrf()) {
            return self::view('church/edit', ['error' => 'Invalid form token.']);
        }

        $id = (int)($_POST['id'] ?? 0);
        $church = Church::find($id);

        if (!$church) {
            return self::view('church/index', [
                'error' => 'Church not found.',
                'churches' => Church::all()
            ]);
        }

        // Collect new data from form
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'address1' => trim($_POST['address1'] ?? ''),
            'address2' => trim($_POST['address2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => strtoupper(trim($_POST['state'] ?? '')),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'latitude' => $church->latitude,
            'longitude' => $church->longitude,
            'is_active' => $church->is_active
        ];

        // Duplicate prevention on update
        // If another church already exists with this (name, city, state, postal_code) block it.
        if (
            Church::exists($data['name'], $data['city'], $data['state'], $data['postal_code']) &&
            ($church->name !== $data['name'] ||
                $church->city !== $data['city'] ||
                $church->state !== $data['state'] ||
                $church->postal_code !== $data['postal_code'])
        ) {
            return self::view('church/edit', [
                'error' => 'Another church with these details already exists.',
                'church' => $church
            ]);
        }

        // Update object and save
        $church->fill($data);
        $ok = $church->save();

        if (!$ok) {
            return self::view('church/edit', [
                'error' => 'Update failed.',
                'church' => $church
            ]);
        }

        return self::view('church/index', [
            'success' => 'Church updated successfully.',
            'churches' => Church::all()
        ]);
    }

    // Handles deletion of a church record
    // Only admin can delete
    public static function destroy()
    {
        self::requireAdmin();

        if (!verify_csrf()) {
            return self::view('church/index', [
                'error' => 'Invalid form token.',
                'churches' => Church::all()
            ]);
        }

        $id = (int)($_POST['id'] ?? 0);
        $church = Church::find($id);

        if (!$church) {
            return self::view('church/index', [
                'error' => 'Church not found.',
                'churches' => Church::all()
            ]);
        }

        $ok = $church->delete();

        if (!$ok) {
            return self::view('church/index', [
                'error' => 'Error deleting record.',
                'churches' => Church::all()
            ]);
        }

        return self::view('church/index', [
            'success' => 'Church deleted successfully.',
            'churches' => Church::all()
        ]);
    }

    // SEARCH CHURCHES (ADMIN AND ORGANIZER)

    public static function search()
    {
        self::requireAdminOrOrganizer();
        $term = trim($_GET['term'] ?? '');
        $results = Church::searchByName($term);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    // ORGANIZER CHURCH REQUEST FORM

    public static function requestForm()
    {
        self::requireAdminOrOrganizer();
        self::view('church/request');
    }

    public static function requestStore()
    {
        self::requireAdminOrOrganizer();

        if (!verify_csrf()) {
            return self::view('church/request', ['error' => 'Invalid form token.']);
        }

        $name  = trim($_POST['name'] ?? '');
        $city  = trim($_POST['city'] ?? '');
        $state = strtoupper(trim($_POST['state'] ?? ''));
        $notes = trim($_POST['notes'] ?? '');

        if ($name === '') {
            return self::view('church/request', ['error' => 'Name is required.']);
        }

        // Save as inactive for admin approval
        $church = new Church();
        $church->fill([
            'name'        => $name,
            'address1'    => '',
            'address2'    => '',
            'city'        => $city,
            'state'       => $state ?: 'TX',
            'postal_code' => '',
            'latitude'    => null,
            'longitude'   => null,
            'is_active'   => 0
        ]);
        $ok = $church->save();

        if (!$ok) {
            return self::view('church/request', ['error' => 'Could not save request.']);
        }

        return self::view('church/request', ['success' => 'Request sent to admin.']);
    }

    // Loads the correct view file and passes data to it
    private static function view(string $name, array $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . "/../Views/{$name}.php";
        require __DIR__ . "/../Views/layout.php";
    }
}
