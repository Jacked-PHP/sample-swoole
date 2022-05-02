<?php


use MyCode\Http\Controllers\AdminController;
use MyCode\Http\Controllers\HomeController;
use MyCode\Http\Controllers\LoginController;
use MyCode\Http\Middlewares\AuthorizationMiddleware;
use MyCode\Http\Middlewares\CheckUsersExistenceMiddleware;
use MyCode\Http\Middlewares\SessionMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/', HomeController::class . ':welcome');

        $group->group('', function (RouteCollectorProxy $group2) {
            $group2->get('/login', LoginController::class . ':login')->setName('login');
            $group2->post('/login', LoginController::class . ':loginHandler')->setName('login-handler');

            $group2->post('/logout', LoginController::class . ':logoutHandler')->setName('logout-handler');

            $group2->get('/admin', AdminController::class . ':admin')
                ->setName('admin');
        })->add(new AuthorizationMiddleware);

        $group->get('/users', HomeController::class . ':showUsers')->setName('show-users');
        $group->get('/users/{id:[0-9]+}', HomeController::class . ':showUser')->add(new
        CheckUsersExistenceMiddleware)->setName('show-user');

    })->add(new SessionMiddleware);
};