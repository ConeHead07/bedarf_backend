<?php

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

/** @var \Laravel\Lumen\Routing\Router $router */
$router; // = new \Laravel\Lumen\Routing\Router();

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/ping', 'PingController@get');
$router->post('/webhooks/github', 'Webhooks\GithubController@receive');
$router->post('/webhooks/github/receive', 'Webhooks\GithubController@receive');
$router->get('/webhooks/github/test', 'Webhooks\GithubController@test');
$router->get('/webhooks/github/pull', 'Webhooks\GithubController@pull');
$router->get('/webhooks/github/branch', 'Webhooks\GithubController@branch');
$router->get('/webhooks/github/date', 'Webhooks\GithubController@date');
$router->get('/webhooks/github/lsAll', 'Webhooks\GithubController@lsAll');
$router->get('/webhooks/github/pwd', 'Webhooks\GithubController@pwd');
$router->get('/webhooks/github/fetchAll', 'Webhooks\GithubController@fetchAll');
$router->get('/webhooks/github/procOpenPull', 'Webhooks\GithubController@procOpenPull');


$router->group([
    'prefix' => 'auth'
], function() use($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
    $router->post('me', 'AuthController@me');
    $router->get('me', 'AuthController@me');
    $router->get('heartbeat', 'AuthController@heartbeat');
});

$router->group(['prefix'=>'api'], function() use($router){
    // Controller-Classes in app/Http/Controllers
    // Call ExampleController->all() in app/Http/Controllers/ExampleController.php
    $router->get('/examples', 'ExampleController@all');
    $router->get('/examples/{id}', 'ExampleController@get');
    $router->get('register', 'AuthController@register');

    $router->group(['prefix' => 'resources'], function() use($router) {
        $router->get('img/{uuid:[a-zA-Z0-9-]+}.jpg', 'ImageController@sendImage');
        $router->get('img/{uuid:[a-zA-Z0-9-]+}', 'ImageController@sendImage');
    });
});

$router->group([
        'prefix'=>'api',
        'middleware' => 'auth:api'
    ], function() use($router){

        $router->group(['prefix' => 'mandant'], function() use($router) {
            $router->get('{id:\d+}', 'MandantController@get');
            $router->get('{id:\d+}/gebaeude', 'MandantController@gebaeude');
            $router->get('get/{id:\d+}', 'MandantController@get');
            $router->get('', 'MandantController@all');
        });

        $router->group(['prefix' => 'byuser'], function() use($router) {
            $router->get('{id:\d+}/inventuren', 'InventurenController@getInventurenByUserId');
        });
    
});

$router->group([
    'prefix'=>'api/admin',
], function() use($router) {
    $router->group(['prefix' => 'login'], function () use ($router) {
        $router->post('auth', 'Admin\LoginController@auth');
        $router->get('logout', 'Admin\LoginController@logout');
        $router->get('', 'Admin\LoginController@login');
    });
});

$router->group([
    'prefix'=>'api/admin',
    'middleware' => 'adminAuth'
], function() use($router) {

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->post('{id:\d+}/update', 'Admin\UserController@update');
        $router->get('{id:\d+}/delete', 'Admin\UserController@delete');
        $router->get('{id:\d+}', 'Admin\UserController@index');
        $router->get('', 'Admin\UserController@listUsers');
        $router->post('checkUser', 'Admin\UserController@checkUser');
        $router->post('create', 'Admin\UserController@create');
    });

    $router->group(['prefix' => 'mandanten'], function () use ($router) {
        $router->post('{id:\d+}/update', 'Admin\MandantenController@update');
        $router->get('{id:\d+}/delete', 'Admin\MandantenController@delete');
        $router->get('{id:\d+}/gebaeude', 'Admin\MandantenController@getGebaeudeList');
        $router->get('{id:\d+}/inventuren', 'Admin\MandantenController@getInventurenList');
        $router->get('{id:\d+}', 'Admin\MandantenController@get');
        $router->post('checkMandant', 'Admin\MandantenController@checkMandant');
        $router->post('create', 'Admin\MandantenController@create');
        $router->get('', 'Admin\MandantenController@index');
    });

    $router->group(['prefix' => 'db'], function () use ($router) {
        // /api/admin/db/showCreate/triggers
        $router->get('showCreate/triggers', 'Admin\Db\ShowCreateController@triggers');

    });

});

$router->group([
    'prefix' => 'api/interviewdata'
], function() use($router) {
    $router->get('all/{id:\d}', 'InterviewBaseDataController@all');
    $router->get('{id:\d+}', 'InterviewBaseDataController@get');
    $router->post('{id:\d+}', 'InterviewBaseDataController@update');
});

$router->group([
    'prefix' => 'api/interviewOverview'
], function() use($router) {
    $router->get('all/{id:\d}', 'InterviewOverviewController@allByKid');
    $router->get('{id:\d+}', 'InterviewOverviewController@get');
    $router->post('{id:\d+}', 'InterviewOverviewController@update');
});

$router->group([
    'prefix' => 'api/taetigkeiten'
], function() use($router) {
    $router->get('all/{id:\d}', 'TaetigkeitenController@all');
    $router->get('{id:\d+}', 'TaetigkeitenController@get');
    $router->post('{id:\d+}', 'TaetigkeitenController@update');
});

$router->group([
    'prefix' => 'api/mobilesArbeiten'
], function() use($router) {
    $router->get('all/{id:\d}', 'MobilesArbeitenController@all');
    $router->get('{id:\d+}', 'MobilesArbeitenController@get');
    $router->post('{id:\d+}', 'MobilesArbeitenController@update');
});

$router->group([
    'prefix' => 'api/mobilesArbeitenAbleitung'
], function() use($router) {
    $router->get('all/{id:\d}', 'MobilesArbeitenAbleitungController@all');
    $router->get('{id:\d+}', 'MobilesArbeitenAbleitungController@get');
    $router->post('{id:\d+}', 'MobilesArbeitenAbleitungController@update');
});
