<?php
// /public_html/index.php - Front Controller unique

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Session;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;

Session::start();

$router = new Router();

$router->get('/login', 'App\Controllers\AuthController@showLogin');
$router->post('/login', 'App\Controllers\AuthController@login');
$router->get('/register', 'App\Controllers\AuthController@showRegister');
$router->post('/register', 'App\Controllers\AuthController@register');
$router->get('/forgot-password', 'App\Controllers\AuthController@showForgotPassword');
$router->post('/forgot-password', 'App\Controllers\AuthController@forgotPassword');
$router->get('/reset-password', 'App\Controllers\AuthController@showResetPassword');
$router->post('/reset-password', 'App\Controllers\AuthController@resetPassword');
$router->get('/logout', 'App\Controllers\AuthController@logout');

$router->get('/portal/{token}', 'App\Controllers\ProjectController@publicPortal');

$router->group([AuthMiddleware::class], function($r) {
    $r->get('/', 'App\Controllers\DashboardController@index');
    $r->get('/focus', 'App\Controllers\DashboardController@focus');
    $r->get('/profile', 'App\Controllers\UserController@profile');
    $r->post('/profile', 'App\Controllers\UserController@updateProfile');

    $r->get('/notifications', 'App\Controllers\NotificationController@index');
    $r->get('/api/notifications/unread-count', 'App\Controllers\NotificationController@unreadCount');
    $r->get('/notifications/{id}/read', 'App\Controllers\NotificationController@markAsRead');

    $r->get('/projects', 'App\Controllers\ProjectController@index');
    $r->get('/projects/create', 'App\Controllers\ProjectController@create');
    $r->post('/projects/store', 'App\Controllers\ProjectController@store');
    $r->get('/projects/{id}', 'App\Controllers\ProjectController@show');
    $r->get('/projects/{id}/edit', 'App\Controllers\ProjectController@edit');
    $r->post('/projects/{id}/update', 'App\Controllers\ProjectController@update');
    $r->post('/projects/{id}/delete', 'App\Controllers\ProjectController@destroy');
    $r->post('/projects/duplicate/{id}', 'App\Controllers\ProjectController@duplicate');

    $r->get('/projects/{project_id}/steps/create', 'App\Controllers\StepController@create');
    $r->post('/steps/store', 'App\Controllers\StepController@store');
    $r->get('/steps/{id}/edit', 'App\Controllers\StepController@edit');
    $r->post('/steps/{id}/update', 'App\Controllers\StepController@update');
    $r->post('/steps/{id}/delete', 'App\Controllers\StepController@destroy');
    $r->post('/api/steps/{id}/toggle', 'App\Controllers\StepController@toggleStatus');
    $r->post('/steps/{id}/grade', 'App\Controllers\StepController@grade');

    $r->get('/steps/{step_id}/tasks/create', 'App\Controllers\TaskController@create');
    $r->post('/tasks/store', 'App\Controllers\TaskController@store');
    $r->get('/tasks/{id}/edit', 'App\Controllers\TaskController@edit');
    $r->post('/tasks/{id}/update', 'App\Controllers\TaskController@update');
    $r->post('/tasks/{id}/delete', 'App\Controllers\TaskController@destroy');
    $r->post('/api/tasks/{id}/status', 'App\Controllers\TaskController@updateStatus');
    $r->post('/tasks/{id}/upload', 'App\Controllers\TaskController@uploadAttachment');

    $r->get('/clients', 'App\Controllers\ClientController@index');
    $r->get('/clients/create', 'App\Controllers\ClientController@create');
    $r->post('/clients/store', 'App\Controllers\ClientController@store');

    $r->post('/risks/store', 'App\Controllers\RiskController@store');
    $r->post('/risks/{id}/delete', 'App\Controllers\RiskController@destroy');

    $r->post('/time/store', 'App\Controllers\TimeEntryController@store');
    $r->get('/billing', 'App\Controllers\TimeEntryController@billing');

    $r->get('/export/project/{id}/csv', 'App\Controllers\ExportController@projectCsv');
    $r->get('/export/project/{id}/pdf', 'App\Controllers\ExportController@projectPdf');

    $r->get('/users', 'App\Controllers\UserController@index');
    $r->get('/users/create', 'App\Controllers\UserController@create');
    $r->post('/users/store', 'App\Controllers\UserController@store');
    $r->get('/users/{id}/edit', 'App\Controllers\UserController@edit');
    $r->post('/users/{id}/update', 'App\Controllers\UserController@update');
    $r->post('/users/{id}/toggle-active', 'App\Controllers\UserController@toggleActive');
    $r->get('/roles/permissions', 'App\Controllers\UserController@permissionsMatrix');
    $r->post('/roles/permissions', 'App\Controllers\UserController@updatePermissions');
    $r->get('/admin/gdpr', 'App\Controllers\UserController@gdprDashboard');
    $r->post('/admin/gdpr/export', 'App\Controllers\UserController@gdprExport');
    $r->post('/admin/gdpr/delete', 'App\Controllers\UserController@gdprDelete');
});

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
