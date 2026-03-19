<?php
declare(strict_types=1);

session_start();

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/src/helpers.php';

use App\Database;
use App\Auth;
use App\Lang;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InvoiceController;
use App\Controllers\ClientController;
use App\Controllers\CompanyController;
use App\Controllers\UserController;
use App\Controllers\SettingsController;

// Init DB
Database::get();

// Language
$lang = $_SESSION['lang'] ?? setting('default_lang', 'id');
Lang::load($lang);

// Route
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Serve uploaded files directly (shouldn't happen via PHP normally, but fallback)
if (str_starts_with($uri, '/uploads/')) {
    $file = BASE_PATH . $uri;
    if (file_exists($file)) {
        $mime = mime_content_type($file);
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

$routes = [
    'GET' => [
        '/'                   => [DashboardController::class, 'index'],
        '/login'              => [AuthController::class, 'loginForm'],
        '/logout'             => [AuthController::class, 'logout'],
        '/lang'               => [AuthController::class, 'setLang'],
        '/invoices'           => [InvoiceController::class, 'index'],
        '/invoices/create'    => [InvoiceController::class, 'create'],
        '/invoices/edit'      => [InvoiceController::class, 'edit'],
        '/invoices/delete'    => [InvoiceController::class, 'delete'],
        '/invoices/pdf'       => [InvoiceController::class, 'pdf'],
        '/invoices/download'  => [InvoiceController::class, 'download'],
        '/invoices/status'    => [InvoiceController::class, 'updateStatus'],
        '/clients'            => [ClientController::class, 'index'],
        '/clients/delete'     => [ClientController::class, 'delete'],
        '/clients/export'     => [ClientController::class, 'export'],
        '/clients/get'        => [ClientController::class, 'get'],
        '/companies'          => [CompanyController::class, 'index'],
        '/companies/delete'   => [CompanyController::class, 'delete'],
        '/companies/default'  => [CompanyController::class, 'setDefault'],
        '/companies/get'      => [CompanyController::class, 'get'],
        '/users'              => [UserController::class, 'index'],
        '/users/delete'       => [UserController::class, 'delete'],
        '/settings'           => [SettingsController::class, 'index'],
        '/formats/get'        => [InvoiceController::class, 'getFormat'],
    ],
    'POST' => [
        '/login'              => [AuthController::class, 'login'],
        '/invoices/save'      => [InvoiceController::class, 'save'],
        '/clients/save'       => [ClientController::class, 'save'],
        '/companies/save'     => [CompanyController::class, 'save'],
        '/users/save'         => [UserController::class, 'save'],
        '/settings/save'      => [SettingsController::class, 'save'],
        '/formats/save'       => [InvoiceController::class, 'saveFormat'],
        '/formats/delete'     => [InvoiceController::class, 'deleteFormat'],
    ],
];

$handler = $routes[$method][$uri] ?? null;

if ($handler === null) {
    http_response_code(404);
    echo '<h1>404 Not Found</h1>';
    exit;
}

[$class, $action] = $handler;
(new $class())->$action();
