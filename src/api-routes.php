<?php

use MyCode\Http\Controllers\Api\UserController;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {
    $group->group('', function (RouteCollectorProxy $group2) {
        $group2->get('/users', UserController::class . ':index')
            ->setName('api-users');
    });
};