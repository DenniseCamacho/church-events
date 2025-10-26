<?php
// index.php
// Front controller and router
// Receives all requests (via .htaccess rewrite) and dispatches them to controllers or views
// Enforces role-based access in controllers, not here

// load helpers and controllers
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/ChurchController.php';
require_once __DIR__ . '/Controllers/ChurchRequestController.php';

// ensure session for auth and CSRF
start_session_once();

// normalize route
// trims any trailing slash so "church/requests/" matches "church/requests"
// default route is "home" if no ?route= was passed (ex: visiting /churchevents/ directly)
$route = $_GET['route'] ?? 'home';
$route = rtrim($route, '/');

// dispatch
switch ($route) {


    // public / landing

    case 'home':
        // render dashboard based on role
        if (!isset($_SESSION['user'])) {
            require __DIR__ . '/Views/home/guest.php';
            break;
        }

        $role = (int)($_SESSION['user']['role_id'] ?? 0);

        if ($role === 1) {
            require __DIR__ . '/Views/home/admin.php';
        } elseif ($role === 2) {
            require __DIR__ . '/Views/home/organizer.php';
        } elseif ($role === 3) {
            require __DIR__ . '/Views/home/volunteer.php';
        } else {
            require __DIR__ . '/Views/home/guest.php';
        }
        break;


    // auth routes


    // show login form
    case 'login':
        AuthController::loginForm();
        break;

    // process login POST
    case 'login/submit':
        AuthController::login();
        break;

    // destroy session
    case 'logout':
        AuthController::logout();
        break;

    // show register form
    case 'register':
        AuthController::registerForm();
        break;

    // process registration POST
    case 'register/submit':
        AuthController::register();
        break;



    // church routes


    // list churches (admin + organizer)
    case 'church':
        ChurchController::index();
        break;

    // show create form (admin only)
    case 'church/create':
        ChurchController::createForm();
        break;

    // handle create POST (admin only)
    case 'church/store':
        ChurchController::store();
        break;

    // edit form (admin only)
    case 'church/edit':
        ChurchController::editForm();
        break;

    // handle update POST (admin only)
    case 'church/update':
        ChurchController::update();
        break;

    // handle delete POST (admin only)
    case 'church/destroy':
        ChurchController::destroy();
        break;



    // church request routes

    // organizers submit a "please add this church" request
    // admins review, approve, reject

    // show request form (organizer or admin)
    case 'church/request':
        ChurchRequestController::requestForm();
        break;

    // handle request submission POST (organizer or admin)
    case 'church/request/store':
        ChurchRequestController::storeRequest();
        break;

    // admin review list of pending church requests
    case 'church/requests':
        ChurchRequestController::reviewList();
        break;

    // approve requested church (admin only)
    case 'church/requests/approve':
        ChurchRequestController::approve();
        break;

    // reject requested church (admin only)
    case 'church/requests/reject':
        ChurchRequestController::reject();
        break;



    // placeholder routes


    // admin user management placeholder
    case 'admin/manage-users':
        // require admin in future controller before exposing real data
        echo "<p>Manage Users (not implemented)</p>";
        break;

    // events placeholder
    case 'events':
        // require admin or organizer in future controller
        echo "<p>Manage Events (not implemented)</p>";
        break;


    // fallback 404

    default:
        // 404 response for unknown routes
        http_response_code(404);

        // basic safe output
        // htmlspecialchars prevents XSS if attacker tampers with ?route=
        echo "Route not found: " . htmlspecialchars($route, ENT_QUOTES, 'UTF-8');
        break;
}
