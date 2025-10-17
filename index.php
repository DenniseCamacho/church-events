<?php
// index.php — routes requests to the right controller or home view.
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
set_security_headers();
require_once __DIR__ . '/Controllers/AuthController.php';

$route  = $_GET['route'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($route === '/' && $method === 'GET') {
    require __DIR__ . '/Views/home.php';
    exit;
}

if ($route === 'auth/login'    && $method === 'GET')  return AuthController::loginForm();
if ($route === 'auth/login'    && $method === 'POST') return AuthController::login();
if ($route === 'auth/logout'   && $method === 'GET')  return AuthController::logout();
if ($route === 'auth/register' && $method === 'GET')  return AuthController::registerForm();
if ($route === 'auth/register' && $method === 'POST') return AuthController::register();

http_response_code(404);
echo "Not Found";
