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

$app->get('/', function () {
    echo '<p><a href="./docs">Go to API Document!</a></p>';
    echo '<p><a href="http://mis.secretariat.taichung.gov.tw/">Go to Front-end APP!(Beta)</a></p>';
});

$app->get('/swagger.json', ['middleware' => 'cors', function() {
    $file = '../resources/docs/swagger.json';
    $content = file_get_contents($file);
    return response($content, 200)->header('Content-Type', 'application/json');
}]);

$app->group(['prefix' => '/v1.0', 'middleware' => 'cors'], function () use ($app) {
    $app->group(['prefix' => '/users'], function () use ($app) {
        $app->group(['middleware' => 'auth'], function () use ($app) {
            $app->get('/', 'UserController@getUsers');
            $app->get('/{uid}', 'UserController@getUsers');
            $app->post('/', 'UserController@register');
            $app->delete('/{uid}', 'UserController@delete');
        });

        $app->patch('/login', 'UserController@login');
    });

    $app->group(['prefix' => '/sfc'], function () use ($app) {
        $app->get('/cities', 'SfcController@getCities');

        $app->group(['middleware' => 'auth'], function () use ($app) {
            $app->patch('/cities/refresh', 'SfcController@refresh');
        });
    });

    $app->group(['prefix' => '/seit'], function() use ($app) {
        $app->group(['middleware' => 'auth'], function () use ($app) {
            $app->get('/mails', 'SeitController@getMails');
            $app->post('/mails/{did}', 'SeitController@insertMails');
            $app->patch('/mails/{mid}', 'SeitController@updateMail');
            $app->patch('/mails/{mid}/send', 'SeitController@sendMail');
            $app->get('/mails/{mid}/ua', 'SeitController@getMailUserAgents');
        });

        $app->get('/images/{jwt}', 'SeitController@showImage');
    });

    $app->group(['prefix' => '/tg'], function () use ($app) {
        $app->get('/gazettes', 'TgController@getGazettes');
        $app->get('/gazettes/{gid}/file', 'TgController@downloadFile');

        $app->group(['middleware' => 'auth'], function () use ($app) {
            $app->patch('/gazettes/refresh', 'TgController@refresh');
            $app->post('/gazettes/{gid}/file', 'TgController@uploadFile');
            $app->delete('/gazettes/{gid}/file', 'TgController@deleteFile');
        });
    });
    
    $app->group(['prefix' => '/tccg'], function () use ($app) {
        $app->group(['middleware' => 'auth'], function () use ($app) {
            $app->get('/directories', 'TccgController@getDirectories');
            $app->patch('/directories/refresh', 'TccgController@updateTccgUsers');
            $app->patch('users/{account}/token/refresh', 'TccgController@refreshToken');
        });
        
        $app->patch('/users/login', 'TccgController@login');
    });
    
    $app->group(['prefix' => '/vote'], function () use ($app) {
        $app->get('/sessions/{vsid}', 'VoteController@getSessions');

        $app->group(['middleware' => 'auth'], function () use ($app) {
            $app->get('/candidates', 'VoteController@getCandidates');
            $app->post('/candidates/{vcid}/voting', 'VoteController@insertVoting');
            
            $app->get('/votings/{tccg_account}', 'VoteController@getVotings');
            $app->get('/result', 'VoteController@getResult');
            $app->patch('/sessions/{vsid}/{type}', 'VoteController@setSessionTime');
        });
    });   
});
