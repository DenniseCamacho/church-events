<?php
// Controller for handling event creation, listing, and basic access for admin and organizer

require_once __DIR__ . '/../Models/Event.php';
require_once __DIR__ . '/../Models/Church.php';
require_once __DIR__ . '/../helpers.php';

class EventController
{
    // allow admin and organizer
    private static function requireAdminOrOrganizer()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if (!in_array($role, [1, 2], true)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // list events
    public static function index()
    {
        self::requireAdminOrOrganizer();
        $role = $_SESSION['user']['role_id'];

        if ($role === 1) {
            $events = Event::all();
        } else {
            $events = Event::byCreator((int)$_SESSION['user']['id']);
        }

        self::view('events/index', ['events' => $events]);
    }

    // show create form
    public static function create()
    {
        self::requireAdminOrOrganizer();
        $churches = Church::approved();
        self::view('events/create', ['churches' => $churches]);
    }

    // handle form submission
    public static function store()
    {
        self::requireAdminOrOrganizer();
        start_session_once();

        if (!verify_csrf()) {
            $churches = Church::approved();
            return self::view('events/create', [
                'error'    => 'Invalid form token.',
                'churches' => $churches
            ]);
        }

        $title             = trim($_POST['title'] ?? '');
        $church_id         = (int)($_POST['church_id'] ?? 0);
        $start_datetime    = trim($_POST['start_datetime'] ?? '');
        $end_datetime      = trim($_POST['end_datetime'] ?? '');
        $location_override = trim($_POST['location_override'] ?? '');

        if ($title === '' || $church_id === 0 || $start_datetime === '') {
            $churches = Church::approved();
            return self::view('events/create', [
                'error'    => 'Title, church, and start date/time are required.',
                'churches' => $churches
            ]);
        }

        $role = $_SESSION['user']['role_id'];
        $status = ($role === 1) ? 'approved' : 'pending';

        $ev = new Event();
        $ev->title = $title;
        $ev->church_id = $church_id;
        $ev->start_datetime = $start_datetime;
        $ev->end_datetime = $end_datetime !== '' ? $end_datetime : null;
        $ev->location_override = $location_override !== '' ? $location_override : null;
        $ev->status = $status;
        $ev->created_by_user_id = (int)$_SESSION['user']['id'];

        $ok = $ev->save();

        if (!$ok) {
            $churches = Church::approved();
            return self::view('events/create', [
                'error'    => 'Error saving event.',
                'churches' => $churches
            ]);
        }

        header('Location: ' . base_url() . '/events');
        exit;
    }

    // load view
    private static function view(string $name, array $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . "/../Views/{$name}.php";
        require __DIR__ . "/../Views/layout.php";
    }
}