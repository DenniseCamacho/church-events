<?php
// FILE: index.php
// PURPOSE: Front controller and router. Dispatches requests to controllers based on ?route= value.

// LOAD HELPERS AND CONTROLLERS
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/ChurchController.php';
require_once __DIR__ . '/Controllers/ChurchRequestController.php';
require_once __DIR__ . '/Controllers/EventController.php';

// ENSURE SESSION FOR AUTH AND CSRF
start_session_once();

// NORMALIZE ROUTE
// Removes trailing slash and sets default route
$route = $_GET['route'] ?? 'home';
$route = rtrim($route, '/');

// ROUTER
switch ($route) {

    // HOME / DASHBOARD
    case 'home':
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

    // AUTH ROUTES
    case 'login':
        AuthController::loginForm();
        break;

    case 'login/submit':
        AuthController::login();
        break;

    case 'logout':
        AuthController::logout();
        break;

    case 'register':
        AuthController::registerForm();
        break;

    case 'register/submit':
        AuthController::register();
        break;

    // CHURCH ROUTES
    case 'church':
        ChurchController::index();
        break;

    case 'church/create':
        ChurchController::createForm();
        break;

    case 'church/store':
        ChurchController::store();
        break;

    case 'church/edit':
        ChurchController::editForm();
        break;

    case 'church/update':
        ChurchController::update();
        break;

    case 'church/destroy':
        ChurchController::destroy();
        break;

    // CHURCH REQUEST ROUTES
    case 'church/request':
        ChurchRequestController::requestForm();
        break;

    case 'church/request/store':
        ChurchRequestController::storeRequest();
        break;

    case 'church/requests':
        ChurchRequestController::reviewList();
        break;

    case 'church/requests/approve':
        ChurchRequestController::approve();
        break;

    case 'church/requests/reject':
        ChurchRequestController::reject();
        break;

    // EVENT ROUTES
    // Admin and organizer can create events
    // Volunteers can only view and click "I want to volunteer"

    case 'events':
        EventController::index();
        break;

    case 'events/create':
        EventController::create();
        break;

    case 'events/store':
        EventController::store();
        break;

    case 'events/volunteer':
        EventController::volunteer();
        break;

    // ADMIN USER MANAGEMENT PLACEHOLDER
    case 'admin/manage-users':
        echo "<p>Manage Users (not implemented)</p>";
        break;

    // FALLBACK 404
    default:
        http_response_code(404);
        echo "Route not found: " . htmlspecialchars($route, ENT_QUOTES, 'UTF-8');
        break;
}
