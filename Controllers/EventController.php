<?php
// FILE: Controllers/EventController.php
// PURPOSE: Controller for events, approvals, public list, volunteer sign-ups

require_once __DIR__ . '/../Models/Event.php';
require_once __DIR__ . '/../Models/Church.php';
require_once __DIR__ . '/../Models/EventVolunteer.php';
require_once __DIR__ . '/../helpers.php';

class EventController
{
    // REQUIRE ADMIN
    private static function requireAdmin()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if ($role !== 1) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // REQUIRE ADMIN OR ORGANIZER
    private static function requireAdminOrOrganizer()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if (!in_array($role, [1, 2], true)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // REQUIRE LOGIN
    private static function requireLogin()
    {
        start_session_once();
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // ROLE-AWARE LIST
    public static function index()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;

        if ($role === 1) {
            $events = Event::allWithCounts();
        } elseif ($role === 2) {
            $events = Event::byCreatorWithCounts((int)$_SESSION['user']['id']);
        } else {
            $events = Event::allApprovedWithCounts();
        }

        self::view('events/index', [
            'events' => $events,
            'role'   => $role
        ]);
    }

    // PUBLIC APPROVED LIST
    public static function public()
    {
        $events = Event::allApprovedWithCounts();
        self::view('events/public', ['events' => $events]);
    }

    // CREATE FORM
    public static function create()
    {
        self::requireAdminOrOrganizer();
        $churches = Church::approved();
        self::view('events/create', ['churches' => $churches]);
    }

    // STORE
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

        $title             = strip_tags(trim($_POST['title'] ?? ''));
        $church_id         = (int)($_POST['church_id'] ?? 0);
        $start_datetime    = trim($_POST['start_datetime'] ?? '');
        $end_datetime      = trim($_POST['end_datetime'] ?? '');
        $location_override = strip_tags(trim($_POST['location_override'] ?? ''));

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
        $ev->duplicate_of_event_id = null;

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

    // organizer/admin view signups for one event
    public static function signups()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $eventId = (int)($_GET['id'] ?? 0);
        if ($eventId <= 0) {
            http_response_code(400);
            die('Invalid event id.');
        }

        // get event
        $event = Event::findWithChurch($eventId);
        if (!$event) {
            http_response_code(404);
            die('Event not found.');
        }

        // organizer should only see their own events
        if ($role === 2 && (int)$event['created_by_user_id'] !== $userId) {
            http_response_code(403);
            die('Access denied');
        }

        // load volunteers
        $volunteers = EventVolunteer::listForEvent($eventId);

        self::view('events/event_signups', [
            'event'      => $event,
            'volunteers' => $volunteers,
        ]);
    }


    // ADMIN VIEW PENDING
    public static function pending()
    {
        self::requireAdmin();
        $events = Event::pending();
        self::view('events/pending', ['events' => $events]);
    }

    // ADMIN APPROVE
    public static function approve()
    {
        self::requireAdmin();
        if (!verify_csrf()) {
            http_response_code(400);
            die('Invalid form token.');
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Event::approve($id);
        }
        header('Location: ' . base_url() . '/events/pending');
        exit;
    }

    // ADMIN REJECT
    public static function reject()
    {
        self::requireAdmin();
        if (!verify_csrf()) {
            http_response_code(400);
            die('Invalid form token.');
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Event::reject($id);
        }
        header('Location: ' . base_url() . '/events/pending');
        exit;
    }

    // VOLUNTEER CLICK
    public static function volunteer()
    {
        self::requireLogin();
        if (!verify_csrf()) {
            http_response_code(400);
            die('Invalid form token.');
        }

        $eventId = (int)($_POST['event_id'] ?? 0);
        $userId  = (int)$_SESSION['user']['id'];

        if ($eventId <= 0) {
            http_response_code(400);
            die('Invalid event.');
        }

        EventVolunteer::join($eventId, $userId);

        header('Location: ' . base_url() . '/events/public');
        exit;
    }

    // VOLUNTEER LIST OWN SIGNUPS
    public static function mySignups()
    {
        self::requireLogin();
        start_session_once();
        $userId = (int)$_SESSION['user']['id'];
        $signups = EventVolunteer::listForUser($userId);
        self::view('events/volunteer_signups', ['signups' => $signups]);
    }

    // VOLUNTEER WITHDRAW
    public static function withdraw()
    {
        self::requireLogin();
        if (!verify_csrf()) {
            http_response_code(400);
            die('Invalid form token.');
        }

        $eventId = (int)($_POST['event_id'] ?? 0);
        $userId  = (int)$_SESSION['user']['id'];

        if ($eventId <= 0) {
            http_response_code(400);
            die('Invalid event.');
        }

        EventVolunteer::withdraw($eventId, $userId);

        header('Location: ' . base_url() . '/events/volunteer_signups');
        exit;
    }

    // VIEW LOADER
    private static function view(string $name, array $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . "/../Views/{$name}.php";
        require __DIR__ . "/../Views/layout.php";
    }
}
