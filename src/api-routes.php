<?php

use MyCode\Http\Controllers\Api\UserController;
use MyCode\Http\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {
    $group->group('', function (RouteCollectorProxy $group2) {
        $group2->get('/users', UserController::class . ':index')
            ->setName('api-users-get');
        $group2->post('/users', UserController::class . ':create')
            ->setName('api-users-create');
        $group2->put('/users/{user_id}', UserController::class . ':update')
            ->setName('api-users-create');
        $group2->delete('/users/{user_id}', UserController::class . ':delete')
            ->setName('api-users-create');
    })->add(new JwtAuthMiddleware);
};