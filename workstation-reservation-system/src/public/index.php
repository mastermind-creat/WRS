<?php
session_start();

// Clean URL router
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request) {
    case '/login':
        require_once '/views/auth/login.php';
        break;
    case '/register':
        require_once '../views/auth/register.php';
        break;
    case '/logout':
        require_once '../views/auth/logout.php';
        break;
    case '/user/dashboard':
        require_once '../views/user/dashboard.php';
        break;
    case '/user/reserve':
        require_once '../views/user/reserve.php';
        break;
    case '/user/my-reservations':
        require_once '../views/user/my_reservations.php';
        break;
    case '/admin/dashboard':
        require_once '../views/admin/dashboard.php';
        break;
    case '/admin/reservations':
        require_once '../views/admin/reservations.php';
        break;
    case '/admin/reports':
        require_once '../views/admin/reports.php';
        break;
    case '/':
    case '':
        require_once '../views/landing.php';
        break;
    default:
        http_response_code(404);
        echo '<h1>404 Not Found</h1>';
        break;
}
?>