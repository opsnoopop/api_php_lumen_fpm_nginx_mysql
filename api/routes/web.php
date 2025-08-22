<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $router->get('/', function () use ($router) {
//     // return $router->app->version();
//     return response()->json(['message' => 'Hello World from Lumen']);
// });

$router->get('/', 'HealthController@index');                   // 1) GET /
$router->post('/users', 'UsersController@store');              // 2) POST /users
$router->get('/users/{id:[0-9]+}', 'UsersController@show');    // 3) GET /users/1
