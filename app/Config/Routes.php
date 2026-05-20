<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('', ['filter' => 'guest'], static function ($routes) {
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::attemptRegister');
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
});
$routes->post('logout', 'AuthController::logout', ['filter' => 'auth']);

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('templates', 'TemplateController::index');
    $routes->post('templates/create', 'TemplateController::store');
});
